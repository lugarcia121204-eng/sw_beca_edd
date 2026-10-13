/** Configuración central de identidad, colores y navegación pública. */
export interface NavItem { label: string; route: string; }
export const theme = {
  identity: { title: 'Sistema EDD', institution: 'CENIDET', subtitle: 'Plataforma Web para la Gestión de Estímulos Docentes del TECNM' },
  colors: { navbarBg: '#1b396a', navbarText: '#ffffff', mobileDrawerBg: '#102344', primary: '#1b396a', primaryDark: '#102344', primaryLight: '#2c5282', accent: '#d97706', success: '#16a34a', text: '#000000', white: '#ffffff', gold: '#ffbf00', darkBackground: '#1a1a2e', darkSurface: '#24243b', darkText: '#e2e8f0' },
  logos: { navbar: '/images/TecnmBlanco.png', sidebar: '/img/LogoTecNMCompleto.png', favicon: '/img/LogoTecNMCerrado.png', alt: 'Logo Institucional TecNM CENIDET' },
  publicNavigation: [
    { label: 'Inicio', route: 'inicio' }, { label: 'Convocatoria', route: 'announcement.show' },
    { label: 'Documentos', route: 'documents.index' }, { label: 'Reconocimientos', route: 'recognitions.search' }, { label: 'Contacto', route: 'contact' },
  ] as NavItem[],
  footer: {
    institution: 'CENIDET', description: 'Centro Nacional de Investigación y Desarrollo Tecnológico', motto: 'Excelencia en Educación Tecnológica',
    copyright: `© ${new Date().getFullYear()} TecNM - CENIDET. Todos los derechos reservados.`,
    contactInfo: { phone: '(777) 362-7770', email: 'contacto@cenidet.tecnm.mx', website: 'www.cenidet.tecnm.mx', address: 'Interior Internado Palmira S/N, Col. Palmira, C.P. 62490, Cuernavaca, Morelos' },
  },
};
export default theme;

/** Aplica la paleta a los componentes y las utilidades globales de Tailwind. */
export function applyTheme() {
  if (typeof document === 'undefined') return;
  const style = document.documentElement.style;
  Object.entries(theme.colors).forEach(([key, value]) => style.setProperty(`--institutional-${key.replace(/[A-Z]/g, c => '-' + c.toLowerCase())}`, value));
  const tokens: Record<string, string> = {
    primary: theme.colors.primary, 'primary-foreground': theme.colors.white, ring: theme.colors.primaryLight,
    accent: theme.colors.accent, 'accent-foreground': theme.colors.white,
    'sidebar-background': theme.colors.primary, sidebar: theme.colors.primary, 'sidebar-foreground': theme.colors.white,
    'sidebar-primary': theme.colors.gold, 'sidebar-primary-foreground': theme.colors.primaryDark,
    'sidebar-accent': theme.colors.primaryLight, 'sidebar-accent-foreground': theme.colors.white,
    'sidebar-border': theme.colors.primaryLight, 'sidebar-ring': theme.colors.gold,
  };
  Object.entries(tokens).forEach(([key, value]) => style.setProperty(`--${key}`, value));
}
