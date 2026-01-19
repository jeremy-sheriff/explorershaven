<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { store } from '@/routes/login';
import { request } from '@/routes/password';
import { Form, Head } from '@inertiajs/vue3';

defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
}>();
</script>

<template>
    <div class="login-container">
        <!-- Left Side - Branding -->
        <div class="login-branding">
            <div class="branding-content">
                <div class="logo-section">
                    <div class="logo-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-12 h-12">
                            <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                            <path d="M6 12v5c3 3 9 3 12 0v-5"/>
                        </svg>
                    </div>
                    <h1 class="brand-name">Explorers Haven</h1>
                    <p class="brand-tagline">Early Childhood Development Center</p>
                </div>

                <div class="branding-features">
                    <div class="feature-item">
                        <div class="feature-icon">✓</div>
                        <div class="feature-text">
                            <h3>Student Management</h3>
                            <p>Comprehensive tracking and records</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">✓</div>
                        <div class="feature-text">
                            <h3>Fee Management</h3>
                            <p>Easy payment tracking and reporting</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">✓</div>
                        <div class="feature-text">
                            <h3>Progress Tracking</h3>
                            <p>Monitor student development</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Login Form -->
        <div class="login-form-container">
            <div class="login-form-wrapper">
                <div class="form-header">
                    <h2 class="form-title">Welcome Back</h2>
                    <p class="form-description">Sign in to your account to continue</p>
                </div>

                <Head title="Log in" />

                <div
                    v-if="status"
                    class="status-message"
                >
                    {{ status }}
                </div>

                <Form
                    v-bind="store.form()"
                    :reset-on-success="['password']"
                    v-slot="{ errors, processing }"
                    class="login-form"
                >
                    <div class="form-fields">
                        <div class="form-group">
                            <Label for="email" class="form-label">Email Address</Label>
                            <Input
                                id="email"
                                type="email"
                                name="email"
                                required
                                autofocus
                                :tabindex="1"
                                autocomplete="email"
                                placeholder="name@example.com"
                                class="form-input"
                            />
                            <InputError :message="errors.email" />
                        </div>

                        <div class="form-group">
                            <div class="form-label-row">
                                <Label for="password" class="form-label">Password</Label>
                                <TextLink
                                    v-if="canResetPassword"
                                    :href="request()"
                                    class="forgot-link"
                                    :tabindex="5"
                                >
                                    Forgot password?
                                </TextLink>
                            </div>
                            <Input
                                id="password"
                                type="password"
                                name="password"
                                required
                                :tabindex="2"
                                autocomplete="current-password"
                                placeholder="Enter your password"
                                class="form-input"
                            />
                            <InputError :message="errors.password" />
                        </div>

                        <div class="remember-me">
                            <Label for="remember" class="remember-label">
                                <Checkbox id="remember" name="remember" :tabindex="3" />
                                <span>Keep me signed in</span>
                            </Label>
                        </div>

                        <Button
                            type="submit"
                            class="submit-button"
                            :tabindex="4"
                            :disabled="processing"
                            data-test="login-button"
                        >
                            <Spinner v-if="processing" class="mr-2" />
                            {{ processing ? 'Signing in...' : 'Sign In' }}
                        </Button>
                    </div>

                    <div class="form-footer" v-if="canRegister">
                        <p class="footer-text">
                            Don't have an account?
                            <TextLink :href="register()" :tabindex="5" class="signup-link">
                                Create account
                            </TextLink>
                        </p>
                    </div>
                </Form>
            </div>

            <div class="login-footer">
                <p>&copy; 2024 Explorers Haven Academy. All rights reserved.</p>
            </div>
        </div>
    </div>
</template>

<style scoped>
.login-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    min-height: 100vh;
    background: hsl(var(--background));
}

@media (max-width: 1024px) {
    .login-container {
        grid-template-columns: 1fr;
    }

    .login-branding {
        display: none;
    }
}

/* Left Side - Branding */
.login-branding {
    background: linear-gradient(135deg, hsl(210 100% 50%) 0%, hsl(220 90% 45%) 100%);
    color: white;
    padding: 4rem;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
}

.login-branding::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 100%;
    height: 100%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    border-radius: 50%;
}

.branding-content {
    position: relative;
    z-index: 1;
    max-width: 500px;
}

.logo-section {
    text-align: center;
    margin-bottom: 4rem;
}

.logo-icon {
    display: inline-block;
    width: 80px;
    height: 80px;
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1.5rem;
    padding: 1rem;
}

.logo-icon svg {
    width: 100%;
    height: 100%;
    color: white;
}

.brand-name {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    letter-spacing: -0.5px;
}

.brand-tagline {
    font-size: 1.125rem;
    opacity: 0.9;
    font-weight: 300;
}

.branding-features {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.feature-item {
    display: flex;
    gap: 1rem;
    align-items: flex-start;
}

.feature-icon {
    width: 32px;
    height: 32px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    flex-shrink: 0;
}

.feature-text h3 {
    font-size: 1.125rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.feature-text p {
    opacity: 0.85;
    font-size: 0.9375rem;
}

/* Right Side - Form */
.login-form-container {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 2rem;
    background: hsl(var(--background));
}

.login-form-wrapper {
    max-width: 440px;
    width: 100%;
    margin: auto;
}

.form-header {
    text-align: center;
    margin-bottom: 2.5rem;
}

.form-title {
    font-size: 2rem;
    font-weight: 700;
    color: hsl(var(--foreground));
    margin-bottom: 0.5rem;
    letter-spacing: -0.5px;
}

.form-description {
    color: hsl(var(--muted-foreground));
    font-size: 0.9375rem;
}

.status-message {
    margin-bottom: 1.5rem;
    padding: 0.875rem;
    background: hsl(142 76% 96%);
    border: 1px solid hsl(142 76% 86%);
    border-radius: 0.5rem;
    text-align: center;
    font-size: 0.875rem;
    font-weight: 500;
    color: hsl(142 76% 36%);
}

.login-form {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.form-fields {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-label {
    font-weight: 500;
    font-size: 0.875rem;
    color: hsl(var(--foreground));
}

.form-label-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.forgot-link {
    font-size: 0.875rem;
    color: hsl(var(--primary));
    text-decoration: none;
    font-weight: 500;
}

.forgot-link:hover {
    text-decoration: underline;
}

.form-input {
    height: 44px;
    font-size: 0.9375rem;
}

.remember-me {
    margin-top: -0.5rem;
}

.remember-label {
    display: flex;
    align-items: center;
    gap: 0.625rem;
    font-weight: 400;
    font-size: 0.875rem;
    cursor: pointer;
    color: hsl(var(--foreground));
}

.submit-button {
    height: 44px;
    font-size: 1rem;
    font-weight: 600;
    margin-top: 0.5rem;
}

.form-footer {
    text-align: center;
    padding-top: 1.5rem;
    border-top: 1px solid hsl(var(--border));
}

.footer-text {
    font-size: 0.875rem;
    color: hsl(var(--muted-foreground));
}

.signup-link {
    color: hsl(var(--primary));
    font-weight: 600;
    text-decoration: none;
    margin-left: 0.25rem;
}

.signup-link:hover {
    text-decoration: underline;
}

.login-footer {
    text-align: center;
    padding-top: 2rem;
    color: hsl(var(--muted-foreground));
    font-size: 0.875rem;
}

@media (max-width: 640px) {
    .login-form-container {
        padding: 1.5rem;
    }

    .form-title {
        font-size: 1.75rem;
    }
}
</style>
