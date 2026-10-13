<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { ArrowLeft, Eye, EyeOff, Info, Mail, Lock, LogIn, GraduationCap } from 'lucide-vue-next';



defineProps<{
    status?: string;
    canResetPassword?: boolean;
    canRegister?: boolean;
}>();

const showPassword = ref(false);

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post('/login', {
        onFinish: () => {
            form.reset('password');
        },
    });
};
</script>

<template>
    <Head title="Iniciar sesión" />
    <main class="login-page">
        <div class="login-container">

            <div class="login-card">
                <section class="form-panel" aria-labelledby="login-title">
                    <div class="form-top"><div class="form-logo"><img src="/images/TecnmLogo.png" alt="Logo TecNM" /><div><strong>TecNM / CENIDET</strong><span class="app-caption">Sistema EDD</span></div></div><Link href="/" class="back-link"><ArrowLeft :size="16" /> Volver al inicio</Link></div><div class="form-body"><h1 id="login-title">Bienvenido</h1><p class="welcome-caption">Ingresa tus credenciales para continuar</p>
                    <p v-if="status" role="status" class="status">{{ status }}</p>
                    <form @submit.prevent="submit">
                        <div class="field">
                            <label for="email">Correo electrónico <span class="required">*</span></label>
                            <div class="email-wrap"><Mail class="field-icon" :size="21" /><input id="email" v-model="form.email" type="email" required autofocus autocomplete="username" placeholder="usuario@cenidet.tecnm.mx" :aria-invalid="!!form.errors.email" aria-describedby="email-help" /></div>
                            <p v-if="form.errors.email" id="email-help" :class="['field-help', { error: form.errors.email }]" :role="form.errors.email ? 'alert' : undefined"><Info :size="13" /> {{ form.errors.email || 'Introduce tu correo electrónico' }}</p>
                        </div>
                        <div class="field">
                            <label for="password">Contraseña <span class="required">*</span></label>
                            <div class="password-wrap"><Lock class="field-icon" :size="21" /><input id="password" v-model="form.password" :type="showPassword ? 'text' : 'password'" required autocomplete="current-password" placeholder="Tu contraseña" :aria-invalid="!!form.errors.password" aria-describedby="password-help" /><button type="button" class="password-toggle" :aria-label="showPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'" :aria-pressed="showPassword" @click="showPassword = !showPassword"><Eye v-if="showPassword" :size="20" /><EyeOff v-else :size="20" /></button></div>
                            <p v-if="form.errors.password" id="password-help" :class="['field-help', { error: form.errors.password }]" :role="form.errors.password ? 'alert' : undefined"><Info :size="13" /> {{ form.errors.password || 'Introduce tu contraseña' }}</p>
                        </div>
                        <div class="form-options"><label class="remember"><input v-model="form.remember" type="checkbox" /> Recordarme</label><Link v-if="canResetPassword" :href="route('password.request')">¿Has olvidado tu contraseña?</Link></div>
                        <button class="submit-button" type="submit" :disabled="form.processing"><LogIn :size="24" />{{ form.processing ? 'Ingresando...' : 'Iniciar Sesión' }}</button>
                    </form><div class="community-divider"><span></span>ó<span></span></div><aside class="community-note"><div class="community-symbol"><GraduationCap :size="28" /></div><div><p>Este sistema es para uso exclusivo de la comunidad <strong>TecNM – CENIDET.</strong></p><p class="community-motto">Sigamos impulsando la educación y la innovación.</p></div></aside></div>
                </section>
                <section class="brand-panel" aria-label="Información del sistema">
                    <div class="brand-content"><div class="brand-mark"><img src="/images/TecnmBlanco.png" alt="" /></div><h2>Sistema de Estímulos al Desempeño del Personal Docente</h2><p>Tecnológico Nacional de México - Reconociendo la excelencia académica y el compromiso de nuestros docentes.</p></div>
                    <div v-if="canRegister" class="register-block"><h3>¿No tienes una cuenta?</h3><p>Únete a la comunidad docente del TecNM</p><Link :href="route('register')" class="register-button">Regístrate</Link></div>
                <footer class="brand-footer">© {{ new Date().getFullYear() }} TecNM / CENIDET - Todos los derechos reservados.</footer></section>
            </div>
        </div>
    </main>
</template>
<style scoped>
.login-page { min-height:100svh; display:flex; align-items:center; justify-content:center; padding:28px 24px; background:#f5f7fa; color:#102344; }
.login-container { width:100%; max-width:1080px; }
.back-link { display:inline-flex; align-items:center; gap:7px; margin-bottom:14px; font-size:13px; color:#52647b; }
.login-card { display:grid; grid-template-columns:1fr 1fr; overflow:hidden; border-radius:18px; background:white; box-shadow:0 12px 32px #10234414; }
.form-panel { padding:36px 40px; }
h1 { margin:0 0 28px; text-align:center; font-size:30px; font-weight:700; color:#1b396a; }
form { display:flex; flex-direction:column; gap:20px; }
.field label { display:block; margin-bottom:9px; font-size:15px; font-weight:500; }
.field input { width:100%; height:46px; padding:0 14px; border:1px solid transparent; border-bottom-color:#cbd5e1; border-radius:9px; background:#f3f4f6; color:#102344; font-size:14px; outline:none; }
.field input:focus { border-color:#1b396a; box-shadow:0 0 0 3px #1b396a12; }
.field input[aria-invalid='true'] { border-color:#b91c1c; }
.field-help { display:flex; align-items:flex-start; gap:5px; margin-top:6px; color:#718096; font-size:11px; line-height:1.5; }
.field-help svg { flex-shrink:0; margin-top:2px; }
.error { color:#b91c1c; }
.password-wrap { position:relative; }
.password-wrap input { padding-right:46px; }
.password-toggle { position:absolute; top:0; right:0; width:44px; height:46px; display:grid; place-items:center; color:#52647b; cursor:pointer; }
.form-options { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px; font-size:12px; }
.remember { display:flex; align-items:center; gap:7px; color:#52647b; }
.remember input { accent-color:#1b396a; }
.form-options a { color:#1b396a; }
.submit-button { height:46px; border-radius:7px; color:white; background:#1b396a; font-weight:600; cursor:pointer; }
.submit-button:hover { background:#102344; }
.submit-button:disabled { opacity:.6; cursor:wait; }
.brand-panel { display:flex; flex-direction:column; justify-content:space-between; gap:32px; padding:42px 38px; color:white; background:linear-gradient(160deg,#10234465,#1b396af2),url('/images/proposito-docente-local.jpg') center/cover; }
.brand-panel h2 { font-size:28px; font-weight:700; line-height:1.22; letter-spacing:-.025em; }
.brand-panel p { margin-top:18px; font-size:16px; line-height:1.7; color:#e2ebf8; }
.register-block { text-align:center; }
.register-block h3 { font-size:21px; font-weight:700; }
.register-block p { margin-top:8px; font-size:14px; }
.register-button { display:inline-flex; justify-content:center; align-items:center; margin-top:18px; min-height:44px; padding:0 28px; border-radius:9px; background:white; color:#1b396a; font-weight:600; }
.status { margin-bottom:18px; font-size:13px; color:#166534; }
button:focus-visible,a:focus-visible { outline:3px solid #93c5fd; outline-offset:3px; }
@media(max-width:900px) { .form-panel { padding:30px; }.brand-panel { padding:34px 28px; }.brand-panel h2 { font-size:25px; } }
@media(max-width:700px) { .login-page { padding:22px 16px; }.login-container { max-width:460px; }.login-card { grid-template-columns:1fr; }.form-panel { padding:28px 24px; }.brand-panel { padding:26px 24px; gap:24px; }.brand-panel h2 { font-size:22px; }.brand-panel p { font-size:14px; } }
</style>

<style scoped>
.login-page { padding:0; background:white; }
.login-container { max-width:none; }
.login-card { min-height:100svh; border-radius:0; box-shadow:none; }
.brand-panel { grid-column:1; grid-row:1; padding:40px 44px 30px; background:radial-gradient(ellipse at top left,#34435c,#142d51 55%,#0e1e38); gap:28px; }
.brand-header { display:flex; align-items:center; gap:15px; }
.brand-header img { width:42px; height:60px; object-fit:contain; }
.brand-header strong { display:block; font-size:22px; font-weight:700; }
.brand-header span { display:block; margin-top:4px; font-size:12px; color:#d3e1f4; }
.brand-content { max-width:560px; margin:auto; text-align:center; }
.brand-mark { display:grid; place-items:center; width:88px; height:100px; border-radius:20px; border:1px solid #ffffff26; background:#ffffff0d; margin:0 auto 30px; }
.brand-mark img { height:70px; width:55px; object-fit:contain; }
.brand-panel h2 { font-size:clamp(28px,2.5vw,40px); }
.brand-content p { font-size:18px; }
.register-block { margin:0 auto auto; }
.brand-footer { border-top:1px solid #ffffff1a; padding-top:18px; text-align:center; font-size:12px; color:#aac7ed; }
.form-panel { grid-column:2; grid-row:1; display:flex; flex-direction:column; padding:48px clamp(28px,5vw,96px) 30px; }
.form-top { display:flex; align-items:center; justify-content:space-between; gap:18px; width:100%; max-width:520px; margin:0 auto; }
.form-logo { display:flex; align-items:center; gap:12px; font-size:15px; color:#1b396a; }
.form-logo img { width:32px; height:46px; object-fit:contain; }
.back-link { margin:0; white-space:nowrap; }
.form-body { width:100%; max-width:520px; margin:auto; padding:40px 0; }
h1 { text-align:left; font-size:34px; margin-bottom:30px; color:#102344; }
form { gap:24px; }
.field input { height:50px; background:white; border:1px solid #cbd5e1; }
.password-toggle { height:50px; }
.submit-button { height:52px; border-radius:9px; }
.form-footer { text-align:center; color:#94a3b8; font-size:12px; }
@media(max-width:900px) { .brand-panel { padding:30px 24px; }.form-panel { padding:30px 24px; }.brand-header strong { font-size:18px; }.brand-content p { font-size:16px; }.form-top { flex-wrap:wrap; } }
@media(max-width:700px) { .login-container { max-width:none; }.login-card { display:flex; flex-direction:column; }.form-panel { min-height:100svh; order:0; }.brand-panel { order:1; padding:32px 24px; }.brand-content { margin:20px auto; }.register-block { margin:0 auto 12px; }.brand-header { justify-content:center; }.brand-mark { display:none; } }
</style>

<style scoped>
.form-panel { align-self:center; width:calc(100% - 48px); max-width:600px; margin:24px auto; padding:28px 38px 26px; border-radius:28px; background:white; }
.login-card { background:#f0f6fc; }
.form-top { position:relative; display:flex; flex-direction:column; padding-top:32px; gap:0; }
.form-top .back-link { position:absolute; right:0; top:0; font-size:12px; color:#1b396a; }
.form-logo { gap:14px; margin:0 auto; font-size:20px; }
.form-logo img { width:42px; height:60px; }
.app-caption { display:block; margin-top:3px; font-size:14px; font-weight:400; color:#809bc1; }
.form-body { padding:24px 0 0; }
h1 { text-align:center; margin:0; font-size:34px; font-weight:700; }
.welcome-caption { text-align:center; color:#8095b4; font-size:14px; margin:8px 0 30px; }
form { gap:20px; }
.field label { font-size:14px; font-weight:600; }
.required { color:#e34c64; }
.email-wrap,.password-wrap { position:relative; }
.field-icon { position:absolute; left:16px; top:15px; color:#7b99bf; pointer-events:none; }
.field input { height:52px; padding-left:48px; border:1px solid #bfd0e8; border-radius:11px; }
.password-toggle { height:52px; color:#7b99bf; }
.form-options { font-size:13px; }
.submit-button { display:flex; align-items:center; justify-content:center; gap:12px; height:54px; background:linear-gradient(110deg,#205398,#0b407b); font-size:18px; border-radius:11px; box-shadow:0 8px 18px #1b396a20; }
.community-divider { display:flex; align-items:center; gap:17px; margin:22px 0 16px; color:#102344; font-size:14px; }
.community-divider span { height:1px; flex:1; background:#bdcfe5; }
.community-note { display:flex; align-items:center; gap:15px; border-radius:17px; padding:16px; background:#e9f3ff; font-size:12px; line-height:1.6; color:#174779; }
.community-symbol { display:grid; place-items:center; width:50px; height:50px; flex-shrink:0; border-radius:50%; background:#c6e2ff; }
.community-note strong { font-weight:700; }
.community-motto { margin-top:6px; font-size:10px; color:#7b95b6; }
.community-motto::before { content:''; display:inline-block; width:30px; height:3px; margin-right:8px; vertical-align:middle; border-radius:2px; background:#ffbf00; }
@media(max-width:1100px) { .form-panel { width:calc(100% - 32px); padding:24px; }.form-logo { font-size:17px; } }
@media(max-width:700px) { .form-panel { min-height:0; margin:24px auto; padding:26px 24px; }.form-top { flex-wrap:nowrap; }.form-body { margin:0; }.brand-panel { border-radius:0; } }
@media(max-width:400px) { .form-panel { padding:24px 18px; }.community-note { gap:10px; padding:12px; }.community-symbol { width:40px; height:40px; } }
</style>

<style scoped>
.brand-panel { padding:30px 36px 24px; gap:22px; }
.brand-content { margin:auto auto 0; }
.brand-mark { width:72px; height:82px; margin-bottom:22px; border-radius:16px; }
.brand-mark img { height:58px; width:44px; }
.brand-panel h2 { font-size:clamp(27px,2.2vw,36px); }
.brand-content p { margin-top:14px; font-size:16px; }
.register-block { margin:0 auto auto; }
.register-block h3 { font-size:19px; }
.register-block p { margin-top:6px; font-size:13px; }
.register-button { margin-top:12px; min-height:40px; }
.form-panel { max-width:560px; padding:22px 30px; margin:20px auto; border-radius:24px; }
.form-top { padding-top:28px; }
.form-logo img { width:36px; height:50px; }
.form-logo { font-size:18px; }
.form-body { padding-top:18px; }
h1 { font-size:30px; }
.welcome-caption { margin:6px 0 22px; font-size:13px; }
form { gap:16px; }
.field input,.password-toggle { height:46px; }
.field-icon { top:12px; }
.submit-button { height:48px; font-size:16px; }
.community-divider { margin:16px 0 12px; }
.community-note { padding:12px; }
.community-symbol { width:42px; height:42px; }
@media(max-width:700px) { .brand-content { margin:0 auto; }.register-block { margin:0 auto; }.brand-panel { padding:28px 24px; }.form-panel { padding:22px; } }
</style>
