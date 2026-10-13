export const getInitials = (name = '') => {
    const tokens = String(name).trim().split(/\s+/).filter(Boolean);

    if (!tokens.length) return '';
    if (tokens.length === 1) return tokens[0].slice(0, 2).toUpperCase();

    return `${tokens[0][0]}${tokens[tokens.length - 1][0]}`.toUpperCase();
};

export const useInitials = () => ({ getInitials });
