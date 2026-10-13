/**
 * Merge class names, handling conditional classes and falsy values
 * Similar to clsx/classnames libraries
 */
export const cn = (...classes) => {
    return classes
        .flat()
        .filter(Boolean)
        .map(item => {
            if (typeof item === 'object') {
                return Object.entries(item)
                    .filter(([_, v]) => v)
                    .map(([k]) => k)
                    .join(' ');
            }
            return String(item);
        })
        .join(' ')
        .trim();
};

/**
 * Convert route name or URL to actual URL string
 */
export const toUrl = (href) => {
    if (!href) return '/';

    // If it's already a string URL
    if (typeof href === 'string') {
        return href;
    }

    // If it's an object with url property (common in Inertia)
    if (typeof href === 'object' && href.url) {
        return href.url;
    }

    // Try to convert to string
    return String(href);
};
