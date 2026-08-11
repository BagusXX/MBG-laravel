# Analisis Modul Report & Transaction (MBG Laravel)

Dokumen ini memetakan hubungan antara **Route**, **Controller**, **View (UI)**, dan **View (PDF)** pada modul **Laporan (Report)** dan **Transaksi (Transaction)**. Fokus utama dokumen ini adalah bagaimana file PDF di-generate dan terhubung ke setiap halaman.

---

## 1. Modul Laporan (Report)
Modul laporan berfokus pada menampilkan rekapitulasi data dan mencetaknya dalam bentuk PDF (atau Excel). 

Pada update terbaru, semua tombol PDF di modul laporan menggunakan metode **Stream (tampil di tab baru browser)** alih-alih langsung diunduh.

| Laporan | Controller | View UI (Tabel) | View PDF (Cetak) | Keterangan |
|---------|------------|-----------------|------------------|------------|
| **Total Operasional** | `ReportPurchaseOperationalNewController` | `report/purchase-operational-new.blade.php` | `report/invoiceReport-purchaseOperational-new.blade.php` | Menampilkan total pengeluaran operasional dengan pembagian 98% dan 2%. |
| **Pembelian Operasional** | `ReportPurchaseOperationalController` | `report/purchase-operational.blade.php` | `report/invoiceReport-purchaseOperational.blade.php` | Rincian detail pembelian dari supplier. |
| **Total Penjualan** | `SalesSummaryNewController` | `report/sales-summary-new.blade.php` | `report/invoiceReport-sales-summary-new.blade.php` | Menampilkan rekap penjualan (dapur) dengan pembagian 98% dan 2%. |
| **Total Penjualan & Selisih** | `SalesSummaryController` | `report/sales-summary.blade.php` | `report/invoiceReport-sales-summary.blade.php` | Menampilkan perbandingan total dapur & total mitra (dengan 85% & 15%). |
| **Penjualan Dapur** | `ReportSalesKitchenController` | `report/sales-kitchen.blade.php` | `report/invoiceReport-sales-kitchen.blade.php` | Laporan rincian penjualan dari sisi Dapur. |
| **Penjualan Mitra** | `ReportSalesPartnerController` | `report/sales-partner.blade.php` | `report/invoiceReport-sales-partner.blade.php` | Laporan rincian penjualan dari sisi Mitra. |
| **Selisih Penjualan** | `ProfitController` | `report/profit.blade.php` | `report/invoiceReport-profit.blade.php` | Laporan hitungan selisih profit antara harga mitra dan harga dapur. |

> **Alur PDF Report:** User menekan tombol `PDF` di halaman laporan -> Request dikirim ke fungsi `pdf()` atau `invoice()` di Controller bersangkutan -> Controller mengambil data -> `Pdf::loadView('nama-file-invoice')` -> `return $pdf->stream()` (Buka tab baru).

---

## 2. Modul Transaksi (Transaction)
Modul transaksi menangani proses operasional (CRUD), seperti pengajuan bahan baku, persetujuan (approval), hingga penjualan.

Ada **dua jenis PDF** pada modul transaksi:
1. **Invoice per-item (Kode Spesifik):** Mencetak struk untuk 1 kode transaksi/pengajuan.
2. **Rekapitulasi (Laporan Transaksi):** Mencetak tabel filter seperti halnya di modul Report.

### A. Transaksi Jual Bahan Baku
| Transaksi | Controller | View UI | View PDF (Per Kode) | View PDF (Rekap Laporan) |
|-----------|------------|---------|---------------------|--------------------------|
| **Dapur** | `SaleMaterialsKitchenController` | `transaction/sale-materials-kitchen.blade.php` | `transaction/invoice-sale-kitchen.blade.php` | `transaction/pdf-sale-materials-kitchen.blade.php` |
| **Mitra** | `SaleMaterialsPartnerController` | `transaction/sale-materials-partner.blade.php` | `transaction/invoice-sale-partner.blade.php` | *(Tidak ada rekap khusus, hanya per kode)* |

### B. Transaksi Pengajuan & Persetujuan (Approval)
Modul ini biasanya diakses oleh Region/Admin untuk menyetujui permintaan dari dapur. PDF yang dicetak adalah struk bukti pengajuan/approval.

| Transaksi | Controller | View UI | View PDF (Cetak Struk) |
|-----------|------------|---------|------------------------|
| **Persetujuan Menu (Approval)** | `SubmissionApprovalController` | `transaction/submissionApproval.blade.php` | `transaction/invoice-submission.blade.php` |
| **Pengajuan Operasional** | `OperationalSubmissionController` | `transaction/operational-submission.blade.php` | `transaction/invoice-operational.blade.php` |
| **Persetujuan Operasional** | `OperationalApprovalController` | `transaction/operational-approval.blade.php` | `transaction/invoice-operational.blade.php` |
| **Pembelian Bahan Baku** | `PurchaseBahanBakuController` | `transaction/purchase-materials.blade.php` | `transaction/invoice-purchase-material.blade.php` |

---

## Kesimpulan Alur (Flow)
1. **Routing:** Jika Anda membuka URL, misalnya `/dashboard/laporan/total-penjualan`, Laravel akan mencari di `routes/web.php` untuk memanggil `SalesSummaryNewController@index`.
2. **View UI:** Controller mengembalikan file `.blade.php` yang berisi form filter tanggal/dapur dan tabel HTML. Di dalam form tersebut terdapat tombol PDF dengan atribut `formtarget="_blank"` (tab baru) dan `formaction=".../pdf"`.
3. **Controller PDF:** Ketika tombol PDF diklik, Laravel memanggil method `pdf()` atau `invoice()` di Controller yang sama.
4. **Generate PDF:** Controller mengambil template PDF (yang sering diawali dengan kata `invoiceReport-` atau berada di dalam folder `transaction/invoice-...`), lalu melakukan injeksi data ke dalam template HTML tersebut dan dirender menjadi file PDF menggunakan package `barryvdh/laravel-dompdf`.
