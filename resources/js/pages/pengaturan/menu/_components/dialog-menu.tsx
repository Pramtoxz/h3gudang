import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { store, update } from '@/routes/pengaturan/menu';
import { useForm } from '@inertiajs/react';
import { Loader2 } from 'lucide-react';
import { useEffect } from 'react';

export interface BarisMenu {
    id: number;
    project_id: number | null;
    nama_menu: string;
    ikon: string | null;
    route: string | null;
    url: string | null;
    parent_id: number | null;
    urutan: number;
    status_aktif: boolean;
    khusus_it: boolean;
    children?: BarisMenu[];
}

interface ProjectPilihan {
    id: number;
    kode: string;
    nama: string;
}

interface DialogMenuProps {
    terbuka: boolean;
    menu: BarisMenu | null;
    parentId: number | null;
    daftarProject: ProjectPilihan[];
    onTutup: () => void;
}

const GLOBAL = 'global';

export function DialogMenu({ terbuka, menu, parentId, daftarProject, onTutup }: DialogMenuProps) {
    const { data, setData, post, put, processing, errors, reset } = useForm({
        project_id: null as number | null,
        nama_menu: '',
        ikon: '',
        route: '',
        url: '',
        parent_id: null as number | null,
        urutan: 0,
        status_aktif: true,
        khusus_it: false,
    });

    useEffect(() => {
        if (!terbuka) return;

        setData({
            project_id: menu?.project_id ?? null,
            nama_menu: menu?.nama_menu ?? '',
            ikon: menu?.ikon ?? '',
            route: menu?.route ?? '',
            url: menu?.url ?? '',
            parent_id: menu?.parent_id ?? parentId,
            urutan: menu?.urutan ?? 0,
            status_aktif: menu?.status_aktif ?? true,
            khusus_it: menu?.khusus_it ?? false,
        });
    }, [terbuka, menu, parentId]);

    const simpan = () => {
        const opsi = {
            onSuccess: () => {
                reset();
                onTutup();
            },
            preserveScroll: true,
        };

        if (menu) {
            put(update(menu.id).url, opsi);

            return;
        }

        post(store().url, opsi);
    };

    return (
        <Dialog open={terbuka} onOpenChange={(status) => !status && onTutup()}>
            <DialogContent className="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>{menu ? 'Edit Menu' : 'Tambah Menu'}</DialogTitle>
                </DialogHeader>

                <div className="grid gap-3">
                    <div className="space-y-1">
                        <Label>
                            Nama Menu <span className="text-destructive">*</span>
                        </Label>
                        <Input
                            value={data.nama_menu}
                            onChange={(event) => setData('nama_menu', event.target.value)}
                        />
                        {errors.nama_menu && (
                            <p className="text-destructive text-xs">{errors.nama_menu}</p>
                        )}
                    </div>

                    <div className="space-y-1">
                        <Label>Project</Label>
                        <Select
                            value={data.project_id ? String(data.project_id) : GLOBAL}
                            onValueChange={(nilai) =>
                                setData('project_id', nilai === GLOBAL ? null : Number(nilai))
                            }
                            disabled={data.parent_id !== null}
                        >
                            <SelectTrigger>
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value={GLOBAL}>Global (semua project)</SelectItem>
                                {daftarProject.map((project) => (
                                    <SelectItem key={project.id} value={String(project.id)}>
                                        {project.nama}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        {data.parent_id !== null && (
                            <p className="text-muted-foreground text-xs">
                                Sub-menu mengikuti project menu induknya.
                            </p>
                        )}
                    </div>

                    <div className="grid grid-cols-2 gap-3">
                        <div className="space-y-1">
                            <Label>Ikon (Lucide)</Label>
                            <Input
                                value={data.ikon}
                                placeholder="Contoh: Store"
                                onChange={(event) => setData('ikon', event.target.value)}
                            />
                        </div>
                        <div className="space-y-1">
                            <Label>Urutan</Label>
                            <Input
                                type="number"
                                min={0}
                                value={data.urutan}
                                onChange={(event) => setData('urutan', Number(event.target.value))}
                            />
                        </div>
                    </div>

                    <div className="grid grid-cols-2 gap-3">
                        <div className="space-y-1">
                            <Label>Nama Route</Label>
                            <Input
                                value={data.route}
                                placeholder="pmo.toko.index"
                                onChange={(event) => setData('route', event.target.value)}
                            />
                        </div>
                        <div className="space-y-1">
                            <Label>URL</Label>
                            <Input
                                value={data.url}
                                placeholder="/pmo/toko"
                                onChange={(event) => setData('url', event.target.value)}
                            />
                        </div>
                    </div>

                    <div className="flex flex-wrap gap-4 pt-1">
                        <label className="flex items-center gap-2 text-sm">
                            <Checkbox
                                checked={data.status_aktif}
                                onCheckedChange={(nilai) => setData('status_aktif', nilai === true)}
                            />
                            Aktif
                        </label>
                        <label className="flex items-center gap-2 text-sm">
                            <Checkbox
                                checked={data.khusus_it}
                                onCheckedChange={(nilai) => setData('khusus_it', nilai === true)}
                            />
                            Khusus IT
                        </label>
                    </div>
                </div>

                <DialogFooter>
                    <Button variant="outline" onClick={onTutup} disabled={processing}>
                        Batal
                    </Button>
                    <Button onClick={simpan} disabled={processing}>
                        {processing && <Loader2 className="mr-1 h-4 w-4 animate-spin" />}
                        Simpan
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
