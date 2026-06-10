<?php declare(strict_types=1); ?>
<head>
<meta charset="utf-8">
<title>Something went wrong!</title>    
<meta name="description" content="Exception">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
<meta name="robots" content="no-index,no-follow">

<style type="text/css">
* {margin: 0;padding: 0;box-sizing: border-box;}
body {font-family: Arial, sans-serif, Helvetica, 'Segoe UI', 'Roboto', sans-serif; /* Web-safe fonts */background-color: #f4f4f4;color: #333;line-height: 1.6;padding: 20px;font-size: 16px;}
.container {max-width:90%px;width:100%;background-color: #fff;padding: 30px;margin: 20px auto;border-radius: 8px;box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1)}
h1 {font-size: 36px;margin-bottom: 20px;color: #e74c3c; /* A soft red for error */}
p {font-size: 18px;color: #555;margin-bottom: 10px 0;}
.error-details {background-color: #f8d7da; /* Light red background */color: #721c24; /* Dark red text */border: 1px solid #f5c6cb;padding: 10px;border-radius: 4px;margin-top: 20px;font-family: 'Courier New', Courier, monospace; /* Monospaced font for error details */font-size: 14px;}
a {color: #3498db; /* Light blue */text-decoration: none;font-weight: bold;}
a:hover {text-decoration: underline;}
footer {display:block;width:100%;margin-top: 30px;font-size: 14px;color: #777;}
footer small {color: #aaa;font-size: 12px;}
@media (max-width: 600px) {h1 {font-size: 28px;} p, .error-details {font-size: 16px;}}
</style>
</head>

<?php
$exception_message = $exception->getMessage();
$exception_type = get_class($exception);
$exception_code = $exception->getCode();
$file = $exception->getFile();
$line = $exception->getLine();
$timestamp = date('Y-m-d H:i:s');
$stack_trace = nl2br($exception->getTraceAsString());
$request_uri = $_SERVER['REQUEST_URI'];
$request_method = $_SERVER['REQUEST_METHOD'];
$client_ip = $_SERVER['REMOTE_ADDR'];
?>

<body>
    <div class="container">
        <h1>Exception: <?= $exception->getMessage() ?> &#x1F635;&#x200D;&#x1F4AB; </h1>
        <p>Sorry, something went wrong while processing your request.</p>

        <!-- Optional: You can display error details dynamically if needed -->
        <div class="error-details">
            <h2>Type: <?= get_class($exception); ?></h2>
            <p>
            <strong>File:</strong> <?= $exception->getFile() ?><br>
            <strong>Line:</strong> <?= $exception->getLine() ?>
            </p>
        </div>

        <div class="error-details">
            <h2>Stack trace</h2>
            <p>
                <pre><?php print_r($exception->getTrace()) ?></pre> 
            </p>
        </div>

        <footer>
            <p>&copy; Crystal Core Framework</p>
        </footer>

    </div>

</body>
</html>