<script setup>
import { Link } from '@inertiajs/vue3';
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { LogIn, Sun, Moon, Menu, X, Phone, Mail, Globe, MapPin } from 'lucide-vue-next';
import theme from '@/config/theme.config';
import { useStyleStore } from '@/stores/style';

defineProps({ canLogin: Boolean, canRegister: Boolean });
const styleStore = useStyleStore();
const menuOpen = ref(false);
const contact = theme.footer.contactInfo;
let desktopQuery;
const closeDesktopMenu = (event) => { if (event.matches) menuOpen.value = false; };
const closeOnEscape = (event) => { if (event.key === 'Escape') menuOpen.value = false; };
onMounted(() => {
    desktopQuery = window.matchMedia('(min-width: 1440px)');
    desktopQuery.addEventListener('change', closeDesktopMenu);
    window.addEventListener('keydown', closeOnEscape);
});
onBeforeUnmount(() => {
    desktopQuery?.removeEventListener('change', closeDesktopMenu);
    window.removeEventListener('keydown', closeOnEscape);
});
</script>

<template>
    <div class="landing-shell flex flex-col font-sans bg-gray-50 text-black dark:bg-[var(--institutional-dark-background)] dark:text-[var(--institutional-dark-text)]">
        <header class="landing-header z-50 shrink-0 shadow-md">
            <div class="header-content w-full 2xl:container mx-auto">
                <Link :href="route('inicio')" class="brand" aria-label="Ir al inicio">
                    <img :src="theme.logos.navbar" :alt="theme.logos.alt" class="brand-logo" />
                    <div class="brand-copy">
                        <p class="brand-title"><span>{{ theme.identity.title }}</span> {{ theme.identity.institution }}</p>
                        <p class="brand-subtitle">{{ theme.identity.subtitle }}</p>
                    </div>
                </Link>
                <nav class="desktop-navigation" aria-label="Navegación principal">
                    <Link v-for="item in theme.publicNavigation" :key="item.route" :href="route(item.route)" :aria-current="route().current(item.route) ? 'page' : undefined">{{ item.label }}</Link>
                </nav>
                <div class="header-actions">
                    <Link :href="route('login')" class="login-button"><LogIn :size="16" /> Iniciar Sesión</Link>
                    <Link :href="route('register')" class="register-button">REGISTRARSE</Link>
                    <div class="appearance-control">
                        <Sun :size="15" aria-hidden="true" />
                        <button type="button" role="switch" :aria-checked="styleStore.darkMode" :aria-label="styleStore.darkMode ? 'Activar tema claro' : 'Activar tema oscuro'" class="theme-switch" @click="styleStore.setDarkMode()"><span :class="{ enabled: styleStore.darkMode }" /></button>
                        <Moon :size="15" aria-hidden="true" />
                    </div>
                </div>
                <button type="button" class="mobile-menu-button" :aria-expanded="menuOpen" aria-controls="public-mobile-menu" aria-label="Abrir o cerrar menú" @click="menuOpen = !menuOpen"><X v-if="menuOpen" /><Menu v-else /></button>
            </div>
            <nav v-if="menuOpen" id="public-mobile-menu" class="mobile-navigation" aria-label="Navegación móvil">
                <div class="mobile-links">
                    <Link v-for="item in theme.publicNavigation" :key="item.route" :href="route(item.route)" :aria-current="route().current(item.route) ? 'page' : undefined" @click="menuOpen = false">{{ item.label }}</Link>
                </div>
                <div class="mobile-appearance">
                    <Sun :size="18" aria-hidden="true" />
                    <button type="button" role="switch" :aria-checked="styleStore.darkMode" :aria-label="styleStore.darkMode ? 'Activar tema claro' : 'Activar tema oscuro'" class="theme-switch" @click="styleStore.setDarkMode()"><span :class="{ enabled: styleStore.darkMode }" /></button>
                    <Moon :size="18" aria-hidden="true" />
                    <span>Modo (Luz / Noche)</span>
                </div>
                <div class="mobile-account-actions">
                    <Link :href="route('register')" class="register-button" @click="menuOpen = false">REGISTRARSE</Link>
                    <Link :href="route('login')" class="login-button" @click="menuOpen = false"><LogIn :size="20" /> Iniciar Sesión</Link>
                </div>
            </nav>
        </header>
        <main class="landing-main"><slot />
        <footer class="landing-footer">
            <div class="footer-container">
                <div class="footer-grid">
                    <section>
                        <h2 class="institution-name">{{ theme.footer.institution }}</h2>
                        <p>{{ theme.footer.description }}</p>
                        <p class="institution-motto">“{{ theme.footer.motto }}”</p>
                    </section>
                    <nav aria-label="Enlaces del pie de página">
                        <h2>Enlaces Rápidos</h2>
                        <Link v-for="item in theme.publicNavigation" :key="item.route" :href="route(item.route)" class="footer-link">{{ item.label }}</Link>
                    </nav>
                    <section>
                        <h2>Contacto</h2>
                        <a :href="'tel:' + contact.phone.replace(/[^0-9+]/g, '')" class="contact-link"><Phone :size="20" /> <span>{{ contact.phone }}</span></a>
                        <a :href="'mailto:' + contact.email" class="contact-link"><Mail :size="20" /> <span>{{ contact.email }}</span></a>
                        <a :href="'https://' + contact.website" target="_blank" rel="noopener noreferrer" class="contact-link"><Globe :size="20" /> <span>{{ contact.website }}</span></a>
                    </section>
                    <section>
                        <h2>Ubicación</h2>
                        <p class="contact-link"><MapPin :size="20" /><span>{{ contact.address }}</span></p>
                    </section>
                </div>
                <div class="footer-copyright">{{ theme.footer.copyright }}</div>
            </div>
        </footer>
        </main>
    </div>
</template>

<style scoped>
.landing-shell { position:fixed; inset:0; height:100dvh; overflow:hidden; }
.landing-main { container-type:size; flex:1 1 0%; min-height:0; overflow-y:auto; overflow-x:hidden; overscroll-behavior-y:none; padding:0; margin:0; }
.landing-header { position:relative; flex-shrink:0; z-index:50; width:100%; background: var(--institutional-navbar-bg); color: var(--institutional-navbar-text); }
.header-content { display:flex; align-items:center; justify-content:flex-start; gap:20px; padding:14px 32px; min-height:76px; }
.brand { display:flex; align-items:center; gap:16px; min-width:0; }
.brand-logo { height:48px; width:34px; object-fit:contain; flex-shrink:0; }
.brand-copy { border-left:1px solid #ffffff40; padding-left:16px; }
.brand-title { font-size:18px; font-weight:700; line-height:1.3; white-space:nowrap; }
.brand-title span,.institution-name { color:var(--institutional-gold); }
.brand-subtitle { font-size:12px; margin-top:2px; white-space:nowrap; }
.desktop-navigation { display:flex; align-items:center; gap:32px; margin-left:auto; }
.desktop-navigation a { font-weight:650; font-size:14px; white-space:nowrap; }
.desktop-navigation a:hover { color:var(--institutional-gold); }
.header-actions { display:flex; align-items:center; gap:10px; flex-shrink:0; }
.login-button,.register-button { display:inline-flex; align-items:center; justify-content:center; gap:7px; padding:7px 12px; border-radius:8px; font-size:14px; font-weight:700; white-space:nowrap; }
.login-button { background:#ffffff18; border:1px solid #ffffff80; }
.login-button:hover { background:#ffffff28; }
.register-button { background:var(--institutional-primary,#1b396a); color:#fff; border:1px solid #ffffff80; }
.register-button:hover { background:var(--institutional-primary-light,#2c5282); }
.appearance-control { display:flex; align-items:center; gap:6px; margin-left:6px; padding-left:12px; border-left:1px solid #ffffff40; }
.theme-switch { width:40px; height:24px; background:#ffffff30; border:1px solid #ffffff50; border-radius:20px; padding:3px; cursor:pointer; }
.theme-switch span { display:block; background:white; width:16px; height:16px; border-radius:50%; transition:transform .2s; }
.theme-switch span.enabled { transform:translateX(16px); }
.mobile-menu-button { margin-left:auto; display:none; cursor:pointer; padding:8px; }
.mobile-navigation { display:none; position:absolute; top:100%; left:24px; right:24px; background:var(--institutional-mobile-drawer-bg); padding:20px; max-height:calc(100dvh - 112px); overflow-y:auto; box-shadow:0 12px 24px #10234435; }
.mobile-links { display:grid; }
.mobile-links a { display:block; padding:18px 20px; font-size:18px; font-weight:650; border-radius:8px; }
.mobile-links a:hover,.mobile-links a[aria-current="page"] { background:#ffffff0a; color:var(--institutional-gold); }
.mobile-appearance { display:flex; align-items:center; gap:10px; padding:18px 20px; margin-top:10px; border-top:1px solid #ffffff20; border-bottom:1px solid #ffffff20; font-size:16px; }
.mobile-appearance span { font-weight:600; }
.mobile-appearance .theme-switch span { font-weight:normal; }
.mobile-account-actions { display:grid; gap:14px; padding-top:14px; }
.mobile-account-actions a { min-height:54px; font-size:17px; border-radius:10px; }
.mobile-menu-button { border:1px solid #ffffff40; border-radius:10px; background:#ffffff10; }
@media(min-width:1440px) { .mobile-navigation { display:none!important; } }

.landing-footer { background:var(--institutional-primary-dark); color:#b8d8ff; }
.footer-container { max-width:1280px; margin:auto; padding:48px 32px 24px; }
.footer-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:32px; padding-bottom:48px; }
.footer-grid h2 { color:white; font-size:16px; font-weight:700; margin-bottom:22px; }
.footer-grid .institution-name { color:var(--institutional-gold); font-size:20px; }
.footer-grid p { line-height:1.55; font-size:14px; }
.institution-motto { font-style:italic; margin-top:10px; font-size:12px!important; }
.footer-link { display:block; margin-bottom:10px; font-size:14px; }
.contact-link { display:flex; align-items:flex-start; gap:10px; margin-bottom:16px; font-size:14px; overflow-wrap:anywhere; }
.contact-link svg { color:var(--institutional-gold); flex-shrink:0; margin-top:4px; }
.footer-link:hover,a.contact-link:hover { color:var(--institutional-gold); }
.footer-copyright { border-top:1px solid var(--institutional-primary-light); padding-top:28px; text-align:center; font-size:12px; }
:global(.dark) .landing-footer, :global(.dark) .landing-footer h2, :global(.dark) .brand-title span { color:var(--institutional-white); }
a:focus-visible,button:focus-visible { outline:2px solid var(--institutional-gold); outline-offset:4px; }
@media(max-width:1650px) { .header-content { gap:16px; padding:14px 32px; } .brand-subtitle { max-width:none; font-size:12px; } .desktop-navigation { gap:28px; } .desktop-navigation a { font-size:14px; } .login-button,.register-button { padding:7px 12px; } }
@media(max-width:1439px) { .header-content { padding:12px 24px; min-height:96px; } .footer-container { padding:48px 24px 24px; } .desktop-navigation,.header-actions { display:none; } .mobile-menu-button { display:block; } .mobile-navigation { display:block; } .brand-subtitle { max-width:none; white-space:normal; } .footer-grid { grid-template-columns:repeat(2,minmax(0,1fr)); } }
@media(max-width:639px) { .mobile-navigation { left:12px; right:12px; padding:14px; } .mobile-links a { padding:14px 12px; font-size:16px; } .mobile-appearance { padding:16px 12px; font-size:14px; } .header-content { padding:12px 16px; } .brand-logo { width:36px; height:46px; } .brand { gap:10px; } .brand-copy { padding-left:10px; } .brand-title { font-size:18px; } .brand-subtitle { font-size:12px; max-width:240px; } .footer-grid { grid-template-columns:1fr; gap:30px; padding-bottom:36px; } .footer-container { padding:48px 16px 24px; } }
</style>
