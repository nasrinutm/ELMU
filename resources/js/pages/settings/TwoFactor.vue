<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, usePage, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { ref, computed } from 'vue';
import { route } from 'ziggy-js';
import axios from 'axios'; // <--- FIXED: Added Import

const props = defineProps<{
    requiresConfirmation?: boolean;
}>();

const page = usePage();
const enabling = ref(false);
const confirming = ref(false);
const disabling = ref(false);
const qrCode = ref<string | null>(null);
const recoveryCodes = ref<string[]>([]);

// FIXED: Tell TypeScript that 'user' has this property by casting to 'any'
const twoFactorEnabled = computed(() => {
    return !enabling.value && (page.props.auth.user as any)?.two_factor_enabled;
});

const confirmationForm = useForm({
    code: '',
});

const enableTwoFactorAuthentication = () => {
    enabling.value = true;

    router.post(route('two-factor.enable'), {}, {
        preserveScroll: true,
        onSuccess: () => Promise.all([
            showQrCode(),
            showRecoveryCodes(),
            confirming.value = props.requiresConfirmation ?? true,
        ]),
        onFinish: () => {
            enabling.value = false;
        },
    });
};

const showQrCode = () => {
    return axios.get(route('two-factor.qr-code')).then((response: any) => { // FIXED: Added 'any' type
        qrCode.value = response.data.svg;
    });
};

const showRecoveryCodes = () => {
    return axios.get(route('two-factor.recovery-codes')).then((response: any) => { // FIXED: Added 'any' type
        recoveryCodes.value = response.data;
    });
};

const confirmTwoFactorAuthentication = () => {
    confirmationForm.post(route('two-factor.confirm'), {
        errorBag: "confirmTwoFactorAuthentication",
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            confirming.value = false;
            qrCode.value = null;
            recoveryCodes.value = [];
        },
    });
};

const regenerateRecoveryCodes = () => {
    axios.post(route('two-factor.recovery-codes'))
        .then(() => showRecoveryCodes());
};

const disableTwoFactorAuthentication = () => {
    disabling.value = true;

    router.delete(route('two-factor.disable'), {
        preserveScroll: true,
        onSuccess: () => {
            disabling.value = false;
            confirming.value = false;
        },
    });
};
</script>

<template>
    <Head title="Two-Factor Authentication" />

    <AppLayout>
        <div class="p-6 bg-[#002B5C] min-h-screen text-white">
            <div class="max-w-4xl mx-auto space-y-8">
                
                <div>
                    <h1 class="text-3xl font-bold text-[#FFD700]">Two-Factor Authentication</h1>
                    <p class="text-gray-300">Add additional security to your account using two-factor authentication.</p>
                </div>

                <Card class="bg-white text-[#002B5C] border-none">
                    <CardHeader>
                        <CardTitle v-if="twoFactorEnabled">
                            You have enabled two-factor authentication.
                        </CardTitle>
                        <CardTitle v-else>
                            You have not enabled two-factor authentication.
                        </CardTitle>
                        <CardDescription>
                            When two-factor authentication is enabled, you will be prompted for a secure, random token during authentication. You may retrieve this token from your phone's Google Authenticator application.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        
                        <div v-if="!twoFactorEnabled && !confirming">
                            <Button @click="enableTwoFactorAuthentication" :disabled="enabling" class="bg-[#FFD700] text-[#002B5C] hover:bg-[#E6C200] font-bold">
                                Enable
                            </Button>
                        </div>

                        <div v-if="confirming">
                            <div class="font-bold mb-4">
                                To finish enabling two-factor authentication, scan the following QR code using your phone's authenticator application or enter the setup key and provide the generated OTP code.
                            </div>

                            <div v-html="qrCode" class="p-4 bg-white inline-block rounded-lg border border-gray-200" />

                            <div class="mt-4">
                                <Label for="code" class="text-[#002B5C] font-bold">Code</Label>
                                <Input
                                    id="code"
                                    v-model="confirmationForm.code"
                                    type="text"
                                    name="code"
                                    class="w-1/2 mt-1 bg-white text-black border-gray-300"
                                    inputmode="numeric"
                                    autofocus
                                    autocomplete="one-time-code"
                                    @keyup.enter="confirmTwoFactorAuthentication"
                                />
                                <p v-if="confirmationForm.errors.code" class="text-red-600 text-sm mt-1">{{ confirmationForm.errors.code }}</p>
                            </div>

                            <div class="mt-4">
                                <Button @click="confirmTwoFactorAuthentication" :class="{ 'opacity-25': confirmationForm.processing }" :disabled="confirmationForm.processing" class="bg-[#002B5C] text-white hover:bg-[#004080]">
                                    Confirm
                                </Button>
                            </div>
                        </div>

                        <div v-if="twoFactorEnabled">
                            <div class="font-bold mb-4">
                                Store these recovery codes in a secure password manager. They can be used to recover access to your account if your two-factor authentication device is lost.
                            </div>

                            <div class="bg-gray-100 rounded-lg p-4 font-mono text-sm grid grid-cols-2 gap-2 max-w-xl text-black">
                                <div v-for="code in recoveryCodes" :key="code">
                                    {{ code }}
                                </div>
                            </div>

                            <div class="mt-4 flex gap-2">
                                <Button @click="regenerateRecoveryCodes" variant="outline" class="border-[#002B5C] text-[#002B5C]">
                                    Regenerate Recovery Codes
                                </Button>
                                <Button @click="disableTwoFactorAuthentication" :disabled="disabling" variant="destructive" class="bg-red-600 hover:bg-red-700 font-bold">
                                    Disable 2FA
                                </Button>
                            </div>
                        </div>

                    </CardContent>
                </Card>

            </div>
        </div>
    </AppLayout>
</template>