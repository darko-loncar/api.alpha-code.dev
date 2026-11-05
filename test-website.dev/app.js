const apiBase = document.querySelector('meta[name="articles-api-base"]').content.replace(/\/+$/, '');
const apiOrigin = new URL(apiBase).origin;
const listView = document.getElementById('article-list-view');
const detailView = document.getElementById('article-detail-view');
const listElement = document.getElementById('article-list');
const detailElement = document.getElementById('article-detail');

async function fetchJson(url) {
    const response = await fetch(url, {
            mode: 'cors',
            credentials: 'omit',
            headers: { Accept: 'application/json' },
        });
    const body = await response.json();
    if (!response.ok) {
        throw new Error(body.error?.message || 'The request could not be completed.');
    }
    return body.data;
}

function formatDate(value) {
    if (!value) return '';
    const date = new Date(value.replace(' ', 'T'));
    return Number.isNaN(date.getTime())
        ? ''
        : new Intl.DateTimeFormat(undefined, { year: 'numeric', month: 'long', day: 'numeric' }).format(date);
}

function excerpt(content, maxLength = 190) {
    const text = new DOMParser().parseFromString(content || '', 'text/html').body.textContent.trim();
    return text.length > maxLength ? `${text.slice(0, maxLength).trimEnd()}…` : text;
}

function setMessage(target, message, isError = false, retry = null) {
    target.replaceChildren();
    const paragraph = document.createElement('p');
    paragraph.className = `status-message${isError ? ' error-message' : ''}`;
    paragraph.textContent = message;
    target.append(paragraph);

    if (retry) {
        const button = document.createElement('button');
        button.className = 'retry-button';
        button.type = 'button';
        button.textContent = 'Try again';
        button.addEventListener('click', retry, { once: true });
        paragraph.append(button);
    }
}

async function loadArticles() {
    listElement.setAttribute('aria-busy', 'true');
    setMessage(listElement, 'Loading articles…');

    try {
        const articles = await fetchJson(`${apiBase}/articles`);
        listElement.replaceChildren();

        if (!Array.isArray(articles) || articles.length === 0) {
            setMessage(listElement, 'There are no published articles yet.');
            return;
        }

        for (const article of articles) {
            const link = document.createElement('a');
            link.className = 'article-card';
            link.href = `#article/${encodeURIComponent(article.id)}`;

            const meta = document.createElement('div');
            meta.className = 'article-meta';
            const date = formatDate(article.published_at || article.created_at);
            const author = [article.user__first_name, article.user__last_name].filter(Boolean).join(' ');
            if (date) meta.append(makeMetaItem(date));
            if (author) meta.append(makeMetaItem(author));

            const title = document.createElement('h2');
            title.className = 'article-title';
            title.textContent = article.title || 'Untitled article';

            const summary = document.createElement('p');
            summary.className = 'article-excerpt';
            summary.textContent = excerpt(article.content || '');

            link.append(meta, title, summary);
            listElement.append(link);
        }
    } catch (error) {
        setMessage(listElement, error.message || 'Unable to load articles.', true, loadArticles);
    } finally {
        listElement.setAttribute('aria-busy', 'false');
    }
}

function makeMetaItem(value) {
    const item = document.createElement('span');
    item.textContent = value;
    return item;
}

function sanitizeContent(html) {
    const parsed = new DOMParser().parseFromString(html || '', 'text/html');
    const allowedTags = new Set([
        'P', 'BR', 'HR', 'H1', 'H2', 'H3', 'H4', 'H5', 'H6',
        'EM', 'STRONG', 'B', 'I', 'U', 'S', 'ABBR', 'UL', 'OL', 'LI',
        'BLOCKQUOTE', 'PRE', 'CODE', 'CITE', 'A', 'IMG', 'TABLE', 'THEAD',
        'TBODY', 'TR', 'TH', 'TD', 'SPAN', 'DIV'
    ]);
    const discardedTags = new Set(['SCRIPT', 'STYLE', 'IFRAME', 'OBJECT', 'EMBED', 'SVG', 'MATH']);

    function cleanNode(node) {
        if (node.nodeType === Node.TEXT_NODE) return document.createTextNode(node.textContent);
        if (node.nodeType !== Node.ELEMENT_NODE) return document.createDocumentFragment();
        if (discardedTags.has(node.tagName)) return document.createDocumentFragment();

        if (!allowedTags.has(node.tagName)) {
            const fragment = document.createDocumentFragment();
            for (const child of node.childNodes) fragment.append(cleanNode(child));
            return fragment;
        }

        const clean = document.createElement(node.tagName.toLowerCase());
        if (node.tagName === 'A') {
            const href = safeUrl(node.getAttribute('href'), true);
            if (href) {
                clean.href = href;
                clean.target = '_blank';
                clean.rel = 'noopener noreferrer';
            }
            copyTextAttribute(node, clean, 'title');
        } else if (node.tagName === 'IMG') {
            const src = safeUrl(node.getAttribute('src'), false);
            if (!src) return document.createDocumentFragment();
            clean.src = src;
            clean.loading = 'lazy';
            copyTextAttribute(node, clean, 'alt');
            copyTextAttribute(node, clean, 'title');
        } else if (node.tagName === 'TH' || node.tagName === 'TD') {
            for (const attribute of ['colspan', 'rowspan']) {
                const value = node.getAttribute(attribute);
                if (/^[1-9]\d?$/.test(value || '')) clean.setAttribute(attribute, value);
            }
        }

        for (const child of node.childNodes) clean.append(cleanNode(child));
        return clean;
    }

    function safeUrl(value, allowMailto) {
        if (!value) return null;
        try {
            const url = new URL(value, apiOrigin);
            if (url.protocol === 'http:' || url.protocol === 'https:') return url.href;
            if (allowMailto && url.protocol === 'mailto:') return url.href;
        } catch {
            return null;
        }
        return null;
    }

    function copyTextAttribute(source, destination, name) {
        const value = source.getAttribute(name);
        if (value) destination.setAttribute(name, value);
    }

    const result = document.createDocumentFragment();
    for (const child of parsed.body.childNodes) result.append(cleanNode(child));
    return result;
}

async function loadArticle(id) {
    detailElement.setAttribute('aria-busy', 'true');
    setMessage(detailElement, 'Loading article…');

    try {
        const article = await fetchJson(`${apiBase}/article/${encodeURIComponent(id)}`);
        detailElement.replaceChildren();

        const header = document.createElement('header');
        header.className = 'article-detail-header';
        const meta = document.createElement('div');
        meta.className = 'article-meta';
        const date = formatDate(article.published_at || article.created_at);
        const author = [article.user__first_name, article.user__last_name].filter(Boolean).join(' ');
        if (date) meta.append(makeMetaItem(date));
        if (author) meta.append(makeMetaItem(author));

        const title = document.createElement('h1');
        title.textContent = article.title || 'Untitled article';
        header.append(meta, title);

        const content = document.createElement('div');
        content.className = 'article-content';
        content.append(sanitizeContent(article.content));
        detailElement.append(header, content);
    } catch (error) {
        setMessage(detailElement, error.message || 'Unable to load this article.', true, () => loadArticle(id));
    } finally {
        detailElement.setAttribute('aria-busy', 'false');
    }
}

function renderRoute() {
    const match = window.location.hash.match(/^#article\/([a-zA-Z0-9_-]+)$/);
    if (match) {
        listView.hidden = true;
        detailView.hidden = false;
        loadArticle(decodeURIComponent(match[1]));
        return;
    }

    detailView.hidden = true;
    listView.hidden = false;
    if (!listElement.dataset.loaded) {
        listElement.dataset.loaded = 'true';
        loadArticles();
    }
}

document.getElementById('back-to-list').addEventListener('click', () => {
    window.location.hash = 'articles';
});
window.addEventListener('hashchange', renderRoute);
renderRoute();
