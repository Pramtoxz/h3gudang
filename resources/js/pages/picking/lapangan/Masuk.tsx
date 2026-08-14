import LogoMA from '@/assets/images/malogo.png';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { prosesMasuk } from '@/routes/picking/lapangan';
import { Head, useForm } from '@inertiajs/react';
import { LogIn } from 'lucide-react';

/**
 * Layar login operator: HP dipakai landscape di lengan, ruang CSS kira-kira
 * 873 x 393 px. Ruang vertikal yang langka, jadi isinya dibagi dua kolom
 * bersebelahan alih-alih ditumpuk ke bawah.
 */
export default function MasukLapangan() {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
        password: '',
        remember: true,
    });

    const kirim = (event: React.FormEvent) => {
        event.preventDefault();
        post(prosesMasuk().url);
    };

    return (
        <>
            <Head title="Masuk Picking" />

            <div className="bg-background flex min-h-screen items-center justify-center p-4">
                <div className="grid w-full max-w-3xl gap-6 sm:grid-cols-[auto_1fr] sm:items-center">
                    <div className="flex flex-col items-center gap-2 sm:w-44">
                        <div className="rounded-2xl bg-white p-3 shadow-md">
                            <img
                                src={LogoMA}
                                alt="PT. Menara Agung"
                                className="size-16 object-contain"
                            />
                        </div>
                        <div className="text-center">
                            <p className="text-base font-semibold">Picking</p>
                            <p className="text-muted-foreground text-xs">PT. Menara Agung</p>
                        </div>
                    </div>

                    <form onSubmit={kirim} className="space-y-3">
                        <div className="space-y-1">
                            <Label htmlFor="email" className="text-sm">
                                Email
                            </Label>
                            <Input
                                id="email"
                                type="email"
                                inputMode="email"
                                autoComplete="username"
                                autoFocus
                                className="h-12 text-base"
                                value={data.email}
                                onChange={(event) => setData('email', event.target.value)}
                            />
                            <InputError message={errors.email} />
                        </div>

                        <div className="space-y-1">
                            <Label htmlFor="password" className="text-sm">
                                Password
                            </Label>
                            <Input
                                id="password"
                                type="password"
                                autoComplete="current-password"
                                className="h-12 text-base"
                                value={data.password}
                                onChange={(event) => setData('password', event.target.value)}
                            />
                            <InputError message={errors.password} />
                        </div>

                        <div className="flex items-center justify-between gap-3">
                            <label className="flex items-center gap-2 text-sm">
                                <Checkbox
                                    checked={data.remember}
                                    onCheckedChange={(nilai) => setData('remember', nilai === true)}
                                />
                                Tetap masuk di HP ini
                            </label>

                            <Button type="submit" size="lg" className="h-12 px-8" disabled={processing}>
                                {processing ? (
                                    <Spinner className="mr-2 size-4" />
                                ) : (
                                    <LogIn className="mr-2 size-4" />
                                )}
                                Masuk
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </>
    );
}
