const ALLOWED_BLOCK_TAGS = new Set([
	'a', 'b', 'strong', 'i', 'em', 'u', 's', 'span',
	'br', 'hr', 'p', 'div', 'blockquote', 'pre', 'code',
	'ul', 'ol', 'li',
	'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
	'table', 'thead', 'tbody', 'tr', 'th', 'td',
	'img', 'figure', 'figcaption',
]);

const ALLOWED_INLINE_TAGS = new Set([
	'a', 'b', 'strong', 'i', 'em', 'u', 's', 'span', 'br', 'code',
]);

const ALLOWED_ATTRIBUTES: Record<string, string[]> = {
	a: ['href', 'title', 'target', 'rel'],
	img: ['src', 'alt', 'width', 'height', 'loading'],
	td: ['colspan', 'rowspan'],
	th: ['colspan', 'rowspan', 'scope'],
	'*': ['class', 'id', 'lang', 'dir'],
};

const SAFE_PROTOCOLS = new Set(['http:', 'https:', 'mailto:']);

const isUrlSafe = (url: string): boolean => {
	try {
		const parsed = new URL(url, window.location.href);
		return SAFE_PROTOCOLS.has(parsed.protocol);
	} catch {
		return false;
	}
};

const sanitizeNode = (node: Node, allowedTags: Set<string>): Node | null => {
	if (node.nodeType === Node.TEXT_NODE) {
		return node.cloneNode();
	}

	if (node.nodeType !== Node.ELEMENT_NODE) {
		return null;
	}

	const element = node as Element;
	const tagName = element.tagName.toLowerCase();

	if (!allowedTags.has(tagName)) {
		const fragment = document.createDocumentFragment();
		node.childNodes.forEach((child) => {
			const sanitized = sanitizeNode(child, allowedTags);
			if (sanitized) {
				fragment.appendChild(sanitized);
			}
		});
		return fragment;
	}

	const newElement = document.createElement(tagName);
	const allowedAttrs = [
		...(ALLOWED_ATTRIBUTES[tagName] ?? []),
		...(ALLOWED_ATTRIBUTES['*'] ?? []),
	];

	allowedAttrs.forEach((attr) => {
		const value = element.getAttribute(attr);

		if (value === null) {
			return;
		}

		if ((attr === 'href' || attr === 'src') && !isUrlSafe(value)) {
			return;
		}

		newElement.setAttribute(attr, value);
	});

	if (tagName === 'a' && newElement.getAttribute('target') === '_blank') {
		newElement.setAttribute('rel', 'noopener noreferrer');
	}

	node.childNodes.forEach((child) => {
		const sanitized = sanitizeNode(child, allowedTags);
		if (sanitized) {
			newElement.appendChild(sanitized);
		}
	});

	return newElement;
};

const toHtmlString = (fragment: DocumentFragment | Node): string => {
	const wrapper = document.createElement('div');
	wrapper.appendChild(fragment.cloneNode(true));
	return wrapper.innerHTML;
};

export const sanitizeHtml = (html: string): string => {
	const doc = new DOMParser().parseFromString(html, 'text/html');
	const fragment = document.createDocumentFragment();

	doc.body.childNodes.forEach((child) => {
		const sanitized = sanitizeNode(child, ALLOWED_BLOCK_TAGS);
		if (sanitized) {
			fragment.appendChild(sanitized);
		}
	});

	return toHtmlString(fragment);
};

export const sanitizeInlineHtml = (html: string): string => {
	const doc = new DOMParser().parseFromString(html, 'text/html');
	const fragment = document.createDocumentFragment();

	doc.body.childNodes.forEach((child) => {
		const sanitized = sanitizeNode(child, ALLOWED_INLINE_TAGS);
		if (sanitized) {
			fragment.appendChild(sanitized);
		}
	});

	return toHtmlString(fragment);
};
