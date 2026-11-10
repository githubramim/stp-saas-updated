<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $student->name_en }} - Application Form</title>
    <style>
        @page {
            size: A4;
            margin: 12mm 15mm 12mm 15mm;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 11px;
            color: #222;
            line-height: 1.4;
            background: #fff;
        }
        .header {
            display: flex;
            align-items: center;
            gap: 14px;
            padding-bottom: 10px;
            border-bottom: 2px solid #1e40af;
            margin-bottom: 10px;
        }
        .header .logo {
            width: 65px;
            height: 65px;
            object-fit: contain;
            border-radius: 6px;
        }
        .header .info { flex: 1; }
        .header .info h1 {
            font-size: 17px;
            color: #1e40af;
            font-weight: 800;
            text-transform: uppercase;
        }
        .header .info p {
            font-size: 10px;
            color: #444;
            margin-top: 2px;
        }
        .header .ref-box {
            text-align: right;
            font-size: 10px;
        }
        .header .ref-box .ref {
            font-weight: 800;
            color: #1e40af;
            font-size: 13px;
        }
        .photo-section {
            text-align: center;
            width: 100px;
        }
        .photo-section .photo-frame {
            width: 80px;
            height: 80px;
            border: 2px solid #333;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            margin: 0 auto 4px;
        }
        .photo-section .photo-frame img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .photo-section .sig-frame {
            width: 100px;
            height: 28px;
            border: 1px solid #999;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            margin: 0 auto 2px;
        }
        .photo-section .sig-frame img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        .photo-section .label {
            font-size: 7px;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        table.info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        table.info-table td {
            padding: 4px 8px;
            border: 1px solid #cbd5e1;
            vertical-align: middle;
        }
        table.info-table td.label {
            width: 20%;
            font-weight: 600;
            color: #555;
            font-size: 10px;
            background: #f8fafc;
        }
        table.info-table td.value {
            width: 30%;
            font-size: 10.5px;
            color: #0f172a;
        }
        .section-title {
            font-size: 11px;
            font-weight: 800;
            color: #1e40af;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 8px 0 4px;
            padding-bottom: 2px;
            border-bottom: 1px solid #93c5fd;
        }
        .signature-area {
            display: flex;
            justify-content: space-between;
            margin-top: 35px;
            padding-top: 10px;
        }
        .signature-area .sig-block {
            text-align: center;
            width: 170px;
        }
        .signature-area .sig-block .sig-space {
            height: 34px;
        }
        .signature-area .sig-block .sig-image {
            height: 34px;
            display: flex;
            align-items: flex-end;
            justify-content: center;
        }
        .signature-area .sig-block .sig-image img {
            max-width: 150px;
            max-height: 32px;
            object-fit: contain;
        }
        .signature-area .sig-block .seal {
            width: 52px;
            height: 52px;
            margin: 0 auto 4px;
            border: 1px dashed #94a3b8;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            font-size: 7px;
            line-height: 1.15;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .signature-area .sig-block .line {
            border-top: 1px solid #333;
            padding-top: 4px;
            font-size: 9.5px;
            font-weight: 600;
        }
        .footer-note {
            margin-top: 25px;
            text-align: center;
            font-size: 8.5px;
            color: #888;
            border-top: 1px dashed #cbd5e1;
            padding-top: 6px;
        }
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="no-print" style="margin-bottom: 12px; text-align: right;">
    <button onclick="window.print()" style="background: #1e40af; color: #fff; border: none; padding: 6px 16px; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: 600;">Print Application</button>
</div>

<div class="header">
    @if($student->institute?->logo)
        <img class="logo" src="{{ asset('storage/' . $student->institute->logo) }}" alt="Logo">
    @endif
    <div class="info">
        <h1>{{ $student->institute?->name ?? 'Skill Development Institute' }}</h1>
        @if($student->institute?->name_bn)
            <p style="font-size: 11px; font-weight: 600; color: #1e40af;">{{ $student->institute->name_bn }}</p>
        @endif
        <p>{{ $student->institute?->address ?? '' }}</p>
        <p>Email: {{ $student->institute?->email ?? '' }} | Mobile: {{ $student->institute?->mobile ?? '' }}</p>
    </div>
    <div class="ref-box">
        <div style="text-transform: uppercase; font-size: 8px; color: #666; letter-spacing: 0.5px;">Application Ref</div>
        <div class="ref">{{ $student->reference_no }}</div>
        <div style="margin-top: 4px; font-size: 8.5px; color: #666;">Date: {{ $student->created_at->format('d/m/Y') }}</div>
        <div style="margin-top: 2px;">
            <span style="display: inline-block; padding: 1px 6px; border-radius: 3px; font-size: 8px; font-weight: bold; text-transform: uppercase;
                {{ $student->status === 'admitted' ? 'background: #dcfce7; color: #15803d;' : ($student->status === 'rejected' ? 'background: #fee2e2; color: #b91c1c;' : 'background: #fef3c7; color: #b45309;') }}">
                Status: {{ $student->status }}
            </span>
        </div>
    </div>
    <div class="photo-section">
        <div class="photo-frame">
            @if($student->photo_path)
                <img src="{{ asset('storage/' . $student->photo_path) }}" alt="Photo">
            @else
                <span style="font-size: 9px; color: #999;">Passport<br>Photo</span>
            @endif
        </div>
        <div class="sig-frame">
            @if($student->signature_path)
                <img src="{{ asset('storage/' . $student->signature_path) }}" alt="Signature">
            @else
                <span style="font-size: 8px; color: #999;">Signature</span>
            @endif
        </div>
        <div class="label">Photo &amp; Signature</div>
    </div>
</div>

{{-- Course and Batch Info --}}
<div class="section-title">Course &amp; Admission Information</div>
<table class="info-table">
    <tr>
        <td class="label">Course Name</td>
        <td class="value" colspan="3"><strong>{{ $student->course?->name }}</strong> @if($student->course?->name_bn) ({{ $student->course->name_bn }}) @endif</td>
    </tr>
    <tr>
        <td class="label">Batch</td>
        <td class="value"><strong>{{ $student->batch?->name ?? 'Not Assigned' }}</strong></td>
        <td class="label">Shift / Timing</td>
        <td class="value">{{ $student->batch?->shift ?? '—' }}</td>
    </tr>
    @if($student->project)
    <tr>
        <td class="label">Project / Scheme</td>
        <td class="value" colspan="3">{{ $student->project->name }} ({{ $student->project->code ?? '—' }})</td>
    </tr>
    @endif
</table>

{{-- Personal Info --}}
<div class="section-title">Personal Information</div>
<table class="info-table">
    <tr>
        <td class="label">Full Name (English)</td>
        <td class="value"><strong>{{ $student->name_en }}</strong></td>
        <td class="label">Full Name (Bangla)</td>
        <td class="value">{{ $student->name_bn ?? '—' }}</td>
    </tr>
    <tr>
        <td class="label">Father's Name (EN)</td>
        <td class="value">{{ $student->father_name_en }}</td>
        <td class="label">Father's Name (BN)</td>
        <td class="value">{{ $student->father_name_bn ?? '—' }}</td>
    </tr>
    <tr>
        <td class="label">Mother's Name (EN)</td>
        <td class="value">{{ $student->mother_name_en }}</td>
        <td class="label">Mother's Name (BN)</td>
        <td class="value">{{ $student->mother_name_bn ?? '—' }}</td>
    </tr>
    <tr>
        <td class="label">Date of Birth</td>
        <td class="value">{{ $student->dob?->format('d M, Y') ?? '—' }}</td>
        <td class="label">Gender</td>
        <td class="value">{{ ucfirst($student->gender ?? '—') }}</td>
    </tr>
    <tr>
        <td class="label">Identity ({{ strtoupper($student->id_type ?? 'NID') }})</td>
        <td class="value"><strong>{{ $student->id_no ?? '—' }}</strong></td>
        <td class="label">Blood Group</td>
        <td class="value">{{ $student->blood_group ?? '—' }}</td>
    </tr>
    <tr>
        <td class="label">Religion</td>
        <td class="value">{{ $student->religion ?? '—' }}</td>
        <td class="label">Nationality</td>
        <td class="value">{{ $student->nationality ?? 'Bangladeshi' }}</td>
    </tr>
    <tr>
        <td class="label">Contact Number</td>
        <td class="value"><strong>{{ $student->contact }}</strong></td>
        <td class="label">Email Address</td>
        <td class="value">{{ $student->email ?? '—' }}</td>
    </tr>
    <tr>
        <td class="label">Guardian Contact</td>
        <td class="value">{{ $student->guardian_contact ?? '—' }} ({{ $student->guardian_relation ?? 'Guardian' }})</td>
        <td class="label">Occupation</td>
        <td class="value">{{ $student->occupation ?? '—' }}</td>
    </tr>
</table>

{{-- Address Details --}}
<div class="section-title">Address Information</div>
<table class="info-table">
    <tr>
        <td class="label" style="width:18%;">Present Address</td>
        <td class="value" colspan="3">
            {{ implode(', ', array_filter([$student->present_village, $student->present_road, $student->present_po, $student->present_upazila, $student->present_district, $student->present_division])) ?: '—' }}
        </td>
    </tr>
    <tr>
        <td class="label" style="width:18%;">Permanent Address</td>
        <td class="value" colspan="3">
            {{ implode(', ', array_filter([$student->perm_village, $student->perm_road, $student->perm_po, $student->perm_upazila, $student->perm_district, $student->perm_division])) ?: '—' }}
        </td>
    </tr>
</table>

{{-- Education --}}
<div class="section-title">Educational Qualification</div>
<table class="info-table">
    <tr>
        <td class="label" style="width:18%;">Degree / Exam</td>
        <td class="value" style="width:32%;">{{ $student->edu_degree ?? '—' }}</td>
        <td class="label" style="width:18%;">Institute Name</td>
        <td class="value" style="width:32%;">{{ $student->edu_institute ?? '—' }}</td>
    </tr>
    <tr>
        <td class="label">Passing Year</td>
        <td class="value">{{ $student->edu_year ?? '—' }}</td>
        <td class="label">GPA / Result</td>
        <td class="value">{{ $student->edu_cgpa ?? '—' }}</td>
    </tr>
</table>

{{-- Physical Signature Area --}}
<div class="signature-area">
    <div class="sig-block">
        @if($student->signature_path)
            <div class="sig-image"><img src="{{ asset('storage/' . $student->signature_path) }}" alt="Student Signature"></div>
        @else
            <div class="sig-space"></div>
        @endif
        <div class="line">
            শিক্ষার্থীর স্বাক্ষর<br>
            <span style="font-size:8.5px;color:#64748b;font-weight:normal;">Student's Signature</span>
        </div>
    </div>
    <div class="sig-block">
        <div class="sig-space"></div>
        <div class="line">
            অভিভাবকের স্বাক্ষর<br>
            <span style="font-size:8.5px;color:#64748b;font-weight:normal;">Guardian's Signature</span>
        </div>
    </div>
    <div class="sig-block">
        <div class="seal">Institute<br>Seal</div>
        <div class="line">
            প্রতিষ্ঠানের স্বাক্ষর ও সিল<br>
            <span style="font-size:8.5px;color:#64748b;font-weight:normal;">Institute's Signature &amp; Seal</span>
        </div>
    </div>
</div>

<div class="footer-note">
    Computer Generated Application Record — {{ $student->institute?->name ?? 'STP SaaS' }} | Printed on {{ now()->format('d/m/Y h:i A') }}
</div>

</body>
</html>
