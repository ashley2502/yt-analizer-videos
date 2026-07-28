import { Form, Head, router } from '@inertiajs/react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { store } from '@/routes/login';

type Props = {
    status?: string;
    canResetPassword: boolean;
};

const DEMO = {
    email: 'hollowgrim@local.test',
    password: 'password',
};

export default function Login({ status }: Props) {
    const [demoLoading, setDemoLoading] = useState(false);

    const enterStudio = () => {
        setDemoLoading(true);
        router.post(
            store.url(),
            { ...DEMO, remember: true },
            {
                onFinish: () => setDemoLoading(false),
            },
        );
    };

    return (
        <>
            <Head title="Entrar">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link
                    href="https://fonts.bunny.net/css?family=bebas-neue:400|dm-sans:400,500,600,700"
                    rel="stylesheet"
                />
            </Head>

            <div className="login-shell relative flex min-h-svh overflow-hidden text-[#e8e2d6]">
                <div
                    aria-hidden
                    className="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_20%_10%,rgba(196,72,54,0.28),transparent_45%),radial-gradient(ellipse_at_90%_80%,rgba(45,120,90,0.18),transparent_40%),linear-gradient(160deg,#0a0908_0%,#161210_48%,#0c0b0a_100%)]"
                />
                <div
                    aria-hidden
                    className="login-grain pointer-events-none absolute inset-0 opacity-[0.35] mix-blend-overlay"
                />
                <div
                    aria-hidden
                    className="pointer-events-none absolute -left-24 top-1/4 h-72 w-72 rounded-full bg-[#c44836]/20 blur-3xl login-orb"
                />
                <div
                    aria-hidden
                    className="pointer-events-none absolute -right-16 bottom-10 h-64 w-64 rounded-full bg-[#2f7a5c]/15 blur-3xl login-orb-delayed"
                />

                <div className="relative z-10 mx-auto flex w-full max-w-6xl flex-col justify-between gap-10 px-6 py-10 md:px-10 lg:flex-row lg:items-end lg:py-14">
                    <section className="flex max-w-xl flex-col gap-5 login-rise">
                        <p className="font-[family-name:var(--login-sans)] text-xs font-semibold tracking-[0.35em] text-[#c44836] uppercase">
                            HollowGrim Beats
                        </p>
                        <h1 className="font-[family-name:var(--login-display)] text-[clamp(3.5rem,12vw,7.5rem)] leading-[0.85] tracking-wide text-[#f3eee4]">
                            YT
                            <br />
                            ANALYZER
                        </h1>
                        <p className="max-w-md font-[family-name:var(--login-sans)] text-base leading-relaxed text-[#b7aea0]">
                            Studio semanal: rachas, briefs, thumbnails y packs
                            YouTube para crecer sin perder el ritmo.
                        </p>
                        <ul className="mt-2 flex flex-wrap gap-x-5 gap-y-2 font-[family-name:var(--login-sans)] text-xs tracking-wide text-[#8f877a] uppercase">
                            <li>2 beats / week</li>
                            <li>4 shorts</li>
                            <li>boom bap oscuro</li>
                        </ul>
                    </section>

                    <section className="w-full max-w-md login-rise-delayed">
                        <div className="border border-[#3a342c]/80 bg-[#12100e]/75 p-6 backdrop-blur-md md:p-8">
                            <div className="mb-6 space-y-1">
                                <h2 className="font-[family-name:var(--login-display)] text-3xl tracking-wide text-[#f3eee4]">
                                    Entrar al studio
                                </h2>
                                <p className="font-[family-name:var(--login-sans)] text-sm text-[#9a9184]">
                                    Acceso directo a tu pipeline de producción.
                                </p>
                            </div>

                            <Button
                                type="button"
                                onClick={enterStudio}
                                disabled={demoLoading}
                                className="mb-5 h-12 w-full rounded-none border-0 bg-[#c44836] font-[family-name:var(--login-sans)] text-sm font-semibold tracking-wide text-[#f8f1ea] uppercase hover:bg-[#d45540]"
                                data-test="demo-login-button"
                            >
                                {demoLoading && <Spinner />}
                                Entrar como HollowGrim
                            </Button>

                            <div className="mb-5 flex items-center gap-3">
                                <div className="h-px flex-1 bg-[#3a342c]" />
                                <span className="font-[family-name:var(--login-sans)] text-[11px] tracking-[0.2em] text-[#6f675c] uppercase">
                                    o con cuenta
                                </span>
                                <div className="h-px flex-1 bg-[#3a342c]" />
                            </div>

                            <Form
                                {...store.form()}
                                resetOnSuccess={['password']}
                                className="flex flex-col gap-4"
                            >
                                {({ processing, errors }) => (
                                    <>
                                        <div className="grid gap-2">
                                            <Label
                                                htmlFor="email"
                                                className="font-[family-name:var(--login-sans)] text-[#cfc5b6]"
                                            >
                                                Email
                                            </Label>
                                            <Input
                                                id="email"
                                                type="email"
                                                name="email"
                                                required
                                                autoFocus
                                                tabIndex={1}
                                                autoComplete="email"
                                                defaultValue={DEMO.email}
                                                placeholder="hollowgrim@local.test"
                                                className="h-11 rounded-none border-[#3a342c] bg-[#0c0b0a] text-[#f3eee4] placeholder:text-[#5c554c] focus-visible:border-[#c44836] focus-visible:ring-[#c44836]/30"
                                            />
                                            <InputError message={errors.email} />
                                        </div>

                                        <div className="grid gap-2">
                                            <Label
                                                htmlFor="password"
                                                className="font-[family-name:var(--login-sans)] text-[#cfc5b6]"
                                            >
                                                Password
                                            </Label>
                                            <PasswordInput
                                                id="password"
                                                name="password"
                                                required
                                                tabIndex={2}
                                                autoComplete="current-password"
                                                defaultValue={DEMO.password}
                                                placeholder="password"
                                                className="h-11 rounded-none border-[#3a342c] bg-[#0c0b0a] text-[#f3eee4] placeholder:text-[#5c554c] focus-visible:border-[#c44836] focus-visible:ring-[#c44836]/30"
                                            />
                                            <InputError
                                                message={errors.password}
                                            />
                                        </div>

                                        <div className="flex items-center gap-3">
                                            <Checkbox
                                                id="remember"
                                                name="remember"
                                                defaultChecked
                                                tabIndex={3}
                                                className="rounded-none border-[#3a342c] data-[state=checked]:bg-[#c44836] data-[state=checked]:text-white"
                                            />
                                            <Label
                                                htmlFor="remember"
                                                className="font-[family-name:var(--login-sans)] text-sm text-[#9a9184]"
                                            >
                                                Recordarme
                                            </Label>
                                        </div>

                                        <Button
                                            type="submit"
                                            className="mt-1 h-12 w-full rounded-none border border-[#f3eee4]/20 bg-transparent font-[family-name:var(--login-sans)] text-sm font-semibold tracking-wide text-[#f3eee4] uppercase hover:bg-[#f3eee4] hover:text-[#0c0b0a]"
                                            tabIndex={4}
                                            disabled={processing}
                                            data-test="login-button"
                                        >
                                            {processing && <Spinner />}
                                            Entrar
                                        </Button>
                                    </>
                                )}
                            </Form>

                            {status && (
                                <p className="mt-4 text-center font-[family-name:var(--login-sans)] text-sm text-[#2f7a5c]">
                                    {status}
                                </p>
                            )}
                        </div>
                    </section>
                </div>

                <style>{`
                    .login-shell {
                        --login-display: 'Bebas Neue', Impact, sans-serif;
                        --login-sans: 'DM Sans', ui-sans-serif, sans-serif;
                    }
                    .login-grain {
                        background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.55'/%3E%3C/svg%3E");
                    }
                    @keyframes login-rise {
                        from { opacity: 0; transform: translateY(18px); }
                        to { opacity: 1; transform: translateY(0); }
                    }
                    @keyframes login-orb {
                        0%, 100% { transform: translate(0, 0) scale(1); }
                        50% { transform: translate(12px, -10px) scale(1.06); }
                    }
                    .login-rise {
                        animation: login-rise 0.7s ease-out both;
                    }
                    .login-rise-delayed {
                        animation: login-rise 0.7s ease-out 0.12s both;
                    }
                    .login-orb {
                        animation: login-orb 9s ease-in-out infinite;
                    }
                    .login-orb-delayed {
                        animation: login-orb 11s ease-in-out 1.2s infinite;
                    }
                `}</style>
            </div>
        </>
    );
}

Login.layout = null;
