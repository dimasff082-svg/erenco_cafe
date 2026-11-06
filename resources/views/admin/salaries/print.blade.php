<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slip Gaji - {{ $salary->payroll_number }} - {{ $salary->user->name ?? 'Karyawan' }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 20mm;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: #262017;
            background: #fff;
            margin: 0;
            padding: 24px;
        }
        .container {
            max-width: 680px;
            margin: 0 auto;
            border: 1px solid #e5dfd3;
            border-radius: 12px;
            padding: 32px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #262017;
            padding-bottom: 16px;
            margin-bottom: 24px;
        }
        .brand-title {
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -0.5px;
            margin: 0;
            color: #262017;
        }
        .brand-subtitle {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 2px;
            color: #c59b27;
            margin: 2px 0 0;
        }
        .slip-title {
            text-align: right;
        }
        .slip-title h2 {
            font-size: 18px;
            margin: 0;
            font-weight: 700;
        }
        .slip-title p {
            font-size: 11px;
            color: #786e60;
            margin: 4px 0 0;
        }
        .meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            background: #faf8f5;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 24px;
            font-size: 12px;
        }
        .meta-item {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
        }
        .meta-label {
            color: #786e60;
        }
        .meta-value {
            font-weight: 600;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            margin-bottom: 24px;
        }
        th, td {
            padding: 10px 12px;
            text-align: left;
        }
        th {
            background: #faf8f5;
            font-weight: 600;
            color: #786e60;
            border-bottom: 1px solid #e5dfd3;
        }
        td {
            border-bottom: 1px solid #f5f2eb;
        }
        .text-right {
            text-align: right;
        }
        .total-row {
            font-size: 15px;
            font-weight: 800;
            background: #fdfaf3;
            border-top: 2px solid #c59b27;
        }
        .footer {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px dashed #e5dfd3;
            font-size: 12px;
        }
        .sign-box {
            text-align: center;
            width: 180px;
        }
        .sign-line {
            margin-top: 60px;
            border-bottom: 1px solid #262017;
            font-weight: 600;
            padding-bottom: 4px;
        }
        .btn-print {
            background: #262017;
            color: #fff;
            border: none;
            padding: 9px 18px;
            font-size: 12px;
            font-weight: 700;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-print:hover {
            background: #000;
        }
        .btn-download {
            background: #c59b27;
            color: #12110e;
            border: none;
            padding: 9px 18px;
            font-size: 12px;
            font-weight: 700;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-download:hover {
            background: #a98119;
        }
        .btn-back {
            background: #f0ebe1;
            color: #4b4235;
            border: 1px solid #dcd4c5;
            padding: 9px 16px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-back:hover {
            background: #e2dbce;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                padding: 0;
            }
            .container {
                border: none;
                padding: 0;
            }
        }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js" integrity="sha512-GsLlZN/3F2ErC5ifS5QtgpiJtWd43JWSuIgh7mbzZ8zBps+dvLusV+eNQATqgA/HdeKFVgA5v3S/cIrLF7QnIg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
</head>
<body>
    <div class="no-print" style="display: flex; justify-content: space-between; align-items: center; max-width: 680px; margin: 0 auto 16px;">
        <a href="javascript:history.back()" class="btn-back">&larr; Kembali</a>
        <div style="display: flex; gap: 8px;">
            <button onclick="downloadPDF()" class="btn-download">
                Unduh Format PDF
            </button>
            <button onclick="window.print()" class="btn-print">
                Cetak / Print
            </button>
        </div>
    </div>

    <div class="container" id="slip-content">
        <div class="header">
            <div>
                <h1 class="brand-title">Fira Cake</h1>
                <p class="brand-subtitle">BAKERY & PASTRY</p>
                <div style="font-size: 10px; color: #786e60; margin-top: 4px;">Jl. Karunrung No. 5, Kota Makassar</div>
            </div>
            <div class="slip-title">
                <h2>SLIP PENGGAJIAN RESMI</h2>
                <p>Nomor: <strong>{{ $salary->payroll_number }}</strong></p>
            </div>
        </div>

        <div class="meta-grid">
            <div>
                <div class="meta-item">
                    <span class="meta-label">Nama Karyawan:</span>
                    <span class="meta-value">{{ $salary->user->name ?? '-' }}</span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Jabatan:</span>
                    <span class="meta-value">{{ $salary->user->roleLabel() ?? '-' }}</span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Periode Gaji:</span>
                    <span class="meta-value">{{ $salary->period_month }}</span>
                </div>
            </div>
            <div>
                <div class="meta-item">
                    <span class="meta-label">Tanggal Bayar:</span>
                    <span class="meta-value">{{ $salary->payment_date?->format('d/m/Y') ?? '-' }}</span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Metode:</span>
                    <span class="meta-value">{{ strtoupper($salary->payment_method) }}</span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Status:</span>
                    <span class="meta-value" style="color: #059669;">{{ strtoupper($salary->payment_status) }}</span>
                </div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Komponen Gaji</th>
                    <th class="text-right">Nominal</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Gaji Pokok</td>
                    <td class="text-right">Rp {{ number_format($salary->basic_salary, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Tunjangan Operasional</td>
                    <td class="text-right" style="color: #059669;">+ Rp {{ number_format($salary->allowance, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Insentif</td>
                    <td class="text-right" style="color: #c59b27;">+ Rp {{ number_format($salary->incentive ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Potongan (BPJS / Keterlambatan / Pinjaman)</td>
                    <td class="text-right" style="color: #e11d48;">- Rp {{ number_format($salary->deductions, 0, ',', '.') }}</td>
                </tr>
                <tr class="total-row">
                    <td>TOTAL GAJI BERSIH (TAKE HOME PAY)</td>
                    <td class="text-right" style="color: #059669;">Rp {{ number_format($salary->net_salary, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        @if($salary->notes)
            <div style="font-size: 11px; color: #786e60; margin-bottom: 24px; padding: 8px 12px; background: #faf8f5; border-radius: 6px;">
                <strong>Catatan:</strong> {{ $salary->notes }}
            </div>
        @endif

        <div class="footer">
            <div class="sign-box">
                <p style="margin: 0; color: #786e60;">Penerima,</p>
                <div class="sign-line">{{ $salary->user->name ?? 'Karyawan' }}</div>
            </div>
            <div class="sign-box">
                <p style="margin: 0; color: #786e60;">Makassar, {{ $salary->payment_date?->format('d M Y') ?? date('d M Y') }}</p>
                <div class="sign-line">Manajemen Fira Cake</div>
            </div>
        </div>
    </div>

    <script>
        function downloadPDF() {
            const element = document.getElementById('slip-content');
            const opt = {
                margin: [10, 10, 10, 10],
                filename: 'Slip-Gaji-{{ $salary->payroll_number }}.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2, useCORS: true },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };
            html2pdf().set(opt).from(element).save();
        }

        window.addEventListener('DOMContentLoaded', function () {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('download') === 'pdf') {
                setTimeout(downloadPDF, 400);
            }
        });
    </script>
</body>
</html>

