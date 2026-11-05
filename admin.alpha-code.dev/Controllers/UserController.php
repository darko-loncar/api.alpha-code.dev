<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Databases\MainDb;
use App\Services\AuthService;
use Crystal\Components\DataTable;
use Crystal\Components\Pager;
use Crystal\Components\Query\Query;
use Crystal\Components\Response;
use Crystal\Components\View;
use Crystal\Support\UUID;
use Throwable;

class UserController
{
    public function __construct(private MainDb $mainDb, private AuthService $auth)
    {
    }

    /** Users main index view. */
    public function index(): string
    {
        $this->auth->requireLogin();

        $currentPage = max(1, (int) ($_GET['page'] ?? 1));
        $itemsPerPage = 15;

        $userQ = Query::select('user')
            ->leftJoin('user_auth', 'id', 'id', ['id', 'identifier', 'last_login_at'])
            ->orderBy('user.id', 'desc')
            ->page($currentPage, $itemsPerPage);

        $userData = $this->mainDb->getAll($userQ->getQuery(), $userQ->getBindings());
        $userCount = (int) $this->mainDb->firstValue($userQ->getCountQuery());

        $userTable = (new DataTable($userData))
            ->addColumn('id', 'ID')
            ->addColumn('first_name', 'First name')
            ->addColumn('last_name', 'Last name')
            ->addColumn('email', 'Email')
            ->addColumn('phone', 'Phone');

        $pager = new Pager($currentPage, $itemsPerPage, $userCount, 2, $_SERVER['REQUEST_URI']);

        return View::render('Users.index', ['dataTable' => $userTable, 'pager_object' => $pager]);
    }

    /** Show the new-user form. */
    public function new(): string
    {
        $this->auth->requireLogin();

        return View::render('Users.new', [
            'userData' => ['first_name' => '', 'last_name' => '', 'email' => '', 'phone' => ''],
            'errors' => [],
        ]);
    }

    /** Create a user and its login credentials. */
    public function actionCreate(): string
    {
        $this->auth->requireLogin();

        [$userData, $errors] = $this->validateUserInput(true);
        if ($errors !== []) {
            return View::render('Users.new', ['userData' => $userData, 'errors' => $errors]);
        }

        $passwordHash = password_hash($userData['password'], PASSWORD_DEFAULT);
        $pdo = $this->mainDb->getConn();

        try {
            $pdo->beginTransaction();

            $insertUser = Query::insert('user')
                ->set('uuid', UUID::generate())
                ->set('first_name', $userData['first_name'])
                ->set('last_name', $userData['last_name'])
                ->set('email', $userData['email'])
                ->set('phone', $userData['phone'])
                ->set('is_active', true);
            $this->mainDb->getAll($insertUser->getQuery(), $insertUser->getBindings());

            $insertAuth = Query::insert('user_auth')
                ->set('user_id', (int) $pdo->lastInsertId())
                ->set('identifier', $userData['email'])
                ->set('password_hash', $passwordHash)
                ->set('is_active', true);
            $this->mainDb->getAll($insertAuth->getQuery(), $insertAuth->getBindings());

            $pdo->commit();
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            return View::render('Users.new', [
                'userData' => $userData,
                'errors' => ['Unable to create the user. Check that the email is not already in use.'],
            ]);
        }

        Response::redirect('/users');
        return '';
    }

    /** Show an existing user in the edit form. */
    public function edit(string $uuid): string
    {
        $this->auth->requireLogin();

        $userData = $this->findUser($uuid);
        if ($userData === null) {
            Response::redirect('/users');
            return '';
        }

        return View::render('Users.edit', ['userData' => $userData, 'errors' => []]);
    }

    /** Update a user's profile and login identifier. */
    public function actionUpdate(string $uuid): string
    {
        $this->auth->requireLogin();

        $existingUser = $this->findUser($uuid);
        if ($existingUser === null) {
            Response::redirect('/users');
            return '';
        }

        [$userData, $errors] = $this->validateUserInput(false);
        if ($errors !== []) {
            return View::render('Users.edit', [
                'userData' => [...$existingUser, ...$userData],
                'errors' => $errors,
            ]);
        }

        $pdo = $this->mainDb->getConn();
        try {
            $pdo->beginTransaction();

            $updateUser = Query::update('user')
                ->set('first_name', $userData['first_name'])
                ->set('last_name', $userData['last_name'])
                ->set('email', $userData['email'])
                ->set('phone', $userData['phone'])
                ->where('uuid', $uuid);
            $this->mainDb->getAll($updateUser->getQuery(), $updateUser->getBindings());

            $updateAuth = Query::update('user_auth')
                ->set('identifier', $userData['email'])
                ->where('user_id', (int) $existingUser['id']);

            if ($userData['password'] !== '') {
                $updateAuth->set('password_hash', password_hash($userData['password'], PASSWORD_DEFAULT));
            }

            $this->mainDb->getAll($updateAuth->getQuery(), $updateAuth->getBindings());
            $pdo->commit();
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            return View::render('Users.edit', [
                'userData' => [...$existingUser, ...$userData],
                'errors' => ['Unable to update the user. Check that the email is not already in use.'],
            ]);
        }

        Response::redirect('/users/' . rawurlencode($uuid));
        return '';
    }

    /** Delete confirmation page. */
    public function delete(string $uuid): string
    {
        $this->auth->requireLogin();
        $userData = $this->findUser($uuid);

        if ($userData === null) {
            Response::redirect('/users');
            return '';
        }

        return View::render('Users.delete', ['userData' => $userData]);
    }

    /** Delete user action. */
    public function actionDelete(string $uuid): void
    {
        $this->auth->requireLogin();
        $this->mainDb->getAll('DELETE FROM `user` WHERE uuid = ?', [$uuid]);
        Response::redirect('/users');
    }

    /** Display a user's profile without editing controls on the list page. */
    public function view(string $uuid): string
    {
        $this->auth->requireLogin();
        $userData = $this->findUser($uuid);

        if ($userData === null) {
            Response::redirect('/users');
            return '';
        }

        return View::render('Users.profile', ['pageId' => 'dashboard', 'userData' => $userData]);
    }

    private function findUser(string $uuid): ?array
    {
        $userQuery = Query::select('user')->where('user.uuid', $uuid);
        return $this->mainDb->first($userQuery->getQuery(), $userQuery->getBindings());
    }

    /** @return array{0: array<string, string>, 1: list<string>} */
    private function validateUserInput(bool $passwordRequired): array
    {
        $userData = [
            'first_name' => trim($_POST['first_name'] ?? ''),
            'last_name' => trim($_POST['last_name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'password' => $_POST['password'] ?? '',
        ];
        $errors = [];

        if ($userData['first_name'] === '') {
            $errors[] = 'First name is required.';
        }
        if ($userData['last_name'] === '') {
            $errors[] = 'Last name is required.';
        }
        if (!filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Enter a valid email address.';
        }
        if ($passwordRequired && $userData['password'] === '') {
            $errors[] = 'Password is required for a new user.';
        }

        return [$userData, $errors];
    }
}
