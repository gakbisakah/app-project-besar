import React, { useState } from "react";
import AppLayout from "@/layouts/app-layout";
import { Head, router } from "@inertiajs/react";
import { route } from "ziggy-js";

// Import Komponen UI
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";
import { Card, CardContent } from "@/components/ui/card";
import { Label } from "@/components/ui/label";
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from "@/components/ui/dialog";

// Import Ikon
import { ArrowLeft, FileText, XCircle, ExternalLink } from "lucide-react";

// Helper Component untuk Form Read-Only
const SideBySideFormField = ({ label, children }) => (
    <div className="flex flex-col md:flex-row md:items-center space-y-1 md:space-y-0 space-x-0 md:space-x-8">
        <label className="text-sm font-medium text-gray-700 md:w-1/4 min-w-[200px] text-left">
            {label}:
        </label>
        <div className="flex-1 w-full">{children}</div>
    </div>
);

const StackedFormField = ({ label, children }) => (
    <div className="flex flex-col space-y-1 mt-4">
        <label className="text-sm font-medium text-gray-700 text-left">
            {label}:
        </label>
        <div className="w-full">{children}</div>
    </div>
);

export default function Detaill({ book }) {
    const [isRejectOpen, setIsRejectOpen] = useState(false);
    const [rejectNote, setRejectNote] = useState("");
    const [isSubmitting, setIsSubmitting] = useState(false);

    if (!book) return <div>Loading data...</div>;
    const links = Array.isArray(book.drive_link) ? book.drive_link : [];

    // --- SUBMIT PENOLAKAN ---
    const submitReject = (e) => {
        e.preventDefault();
        setIsSubmitting(true);

        router.post(
            route("regis-semi.reject", book.id),
            { note: rejectNote },
            {
                onSuccess: () => {
                    setIsRejectOpen(false);
                    setIsSubmitting(false);
                    setRejectNote("");
                },
                onError: () => setIsSubmitting(false),
            }
        );
    };

    return (
        <AppLayout>
            <Head title={`Verifikasi - ${book.title}`} />

            <div className="max-w-7xl mx-auto p-4 md:px-8 space-y-6 pb-20">
                {/* HEADER */}
                <div className="flex items-center gap-4">
                    <Button
                        variant="outline"
                        size="icon"
                        onClick={() => router.visit(route("regis-semi.index"))}
                    >
                        <ArrowLeft className="h-4 w-4" />
                    </Button>
                    <div>
                        <h1 className="text-2xl font-bold">Verifikasi Buku</h1>
                        <p className="text-sm text-gray-500">Pengusul: {book.dosen}</p>
                    </div>
                </div>

                {/* DETAIL BUKU */}
                <Card>
                    <CardContent className="p-6 space-y-4">
                        <SideBySideFormField label="Judul Buku">
                            <Input value={book.title || ""} readOnly className="bg-gray-50" />
                        </SideBySideFormField>
                        <SideBySideFormField label="ISBN">
                            <Input value={book.isbn || ""} readOnly className="bg-gray-50 font-mono" />
                        </SideBySideFormField>
                        <SideBySideFormField label="Penerbit">
                            <Input value={book.publisher || ""} readOnly className="bg-gray-50" />
                        </SideBySideFormField>

                        {/* LIST DOKUMEN */}
                        <div className="mt-8 pt-4 border-t">
                            <h3 className="font-semibold mb-4 text-gray-900">Dokumen Pendukung</h3>
                            {links.length > 0 ? (
                                links.map((link, idx) => (
                                    <StackedFormField key={idx} label={`Dokumen #${idx + 1}`}>
                                        <div className="flex gap-2">
                                            <Input
                                                value={link}
                                                readOnly
                                                className="bg-gray-50 text-blue-600 underline cursor-pointer"
                                            />
                                            <a href={link} target="_blank" rel="noreferrer">
                                                <Button variant="outline" size="icon">
                                                    <ExternalLink className="h-4 w-4" />
                                                </Button>
                                            </a>
                                        </div>
                                    </StackedFormField>
                                ))
                            ) : (
                                <p className="text-sm text-gray-500 italic">Tidak ada link dokumen.</p>
                            )}
                        </div>
                    </CardContent>
                </Card>

                {/* TOMBOL UTAMA */}
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {/* 1. Buka Dokumen */}
                    <Button variant="secondary" className="h-12 border border-gray-200">
                        <FileText className="mr-2 h-5 w-5" /> Buka Folder Dokumen Pendukung
                    </Button>

                    {/* 2. Tolak Aku */}
                    <Button
                        onClick={() => setIsRejectOpen(true)}
                        className="bg-red-600 hover:bg-red-700 h-12 text-white"
                        disabled={book.status === "REJECTED"}
                    >
                        <XCircle className="mr-2 h-5 w-5" /> Tolak 
                    </Button>
                </div>
            </div>

            {/* POPUP Tolak */}
            <Dialog open={isRejectOpen} onOpenChange={setIsRejectOpen}>
                <DialogContent className="sm:max-w-[500px]">
                    <DialogHeader>
                        <DialogTitle className="text-red-600">Tolak Aku Pengajuan</DialogTitle>
                        <DialogDescription>
                            Berikan alasan penolakan agar dosen dapat memperbaikinya.
                        </DialogDescription>
                    </DialogHeader>
                    <form onSubmit={submitReject} className="space-y-4 py-4">
                        <div className="grid gap-2">
                            <Label htmlFor="note">Alasan Penolakan / Revisi</Label>
                            <Textarea
                                id="note"
                                placeholder="Contoh: Dokumen scan tidak terbaca, mohon upload ulang..."
                                className="min-h-[120px]"
                                value={rejectNote}
                                onChange={(e) => setRejectNote(e.target.value)}
                                required
                                autoFocus
                            />
                        </div>
                        <DialogFooter>
                            <Button type="button" variant="outline" onClick={() => setIsRejectOpen(false)}>
                                Batal
                            </Button>
                            <Button type="submit" variant="destructive" disabled={isSubmitting || !rejectNote}>
                                {isSubmitting ? "Mengirim..." : "Kirim Penolakan"}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </AppLayout>
    );
}
