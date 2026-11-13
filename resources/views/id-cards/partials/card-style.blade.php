{{-- Shared CR80 card face styles, used by the print sheet and the design editor preview. --}}
<style>
    /* ===== CR80 card: 85.6mm × 53.98mm ===== */
    .idc {
        position: relative;
        width: 85.6mm;
        height: 53.98mm;
        border-radius: 3mm;
        overflow: hidden;
        background: #ffffff;
        color: #0f172a;
        box-shadow: 0 1.5mm 3mm rgba(15, 23, 42, .18);
        break-inside: avoid;
        page-break-inside: avoid;
    }
    .idc-band {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 17mm;
    }
    .idc-head {
        position: relative;
        display: flex;
        align-items: center;
        gap: 2.5mm;
        height: 17mm;
        padding: 2.5mm 3.5mm;
        color: #ffffff;
    }
    .idc-logo {
        flex: 0 0 auto;
        width: 9mm;
        height: 9mm;
        border-radius: 2mm;
        object-fit: contain;
        background: #ffffff;
        padding: .8mm;
    }
    .idc-logo-fallback {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        background: rgba(255, 255, 255, .18);
        border: .3mm solid rgba(255, 255, 255, .5);
        color: #ffffff;
        font-size: 7pt;
        font-weight: 800;
        letter-spacing: .04em;
    }
    .idc-head-text { min-width: 0; }
    .idc-institute {
        font-size: 7.4pt;
        font-weight: 800;
        line-height: 1.1;
        text-transform: uppercase;
        letter-spacing: .035em;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .idc-title {
        font-size: 6.2pt;
        font-weight: 600;
        line-height: 1.25;
        text-transform: uppercase;
        letter-spacing: .05em;
        opacity: .92;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .idc-subtitle {
        font-size: 5.2pt;
        line-height: 1.3;
        opacity: .8;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .idc-body {
        position: relative;
        display: flex;
        gap: 2.5mm;
        height: 31mm;
        padding: 2.5mm 3.5mm 0;
    }
    .idc-body-qr { padding-right: 20mm; }
    .idc-photo {
        flex: 0 0 auto;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 17.5mm;
        height: 21mm;
        overflow: hidden;
        border: .5mm solid #e2e8f0;
        border-radius: 2mm;
        background: #f1f5f9;
    }
    .idc-photo img { width: 100%; height: 100%; object-fit: cover; }
    .idc-photo span {
        font-size: 15pt;
        font-weight: 800;
        color: #64748b;
        letter-spacing: .03em;
    }
    .idc-info { flex: 1 1 auto; min-width: 0; }
    .idc-name {
        font-size: 9pt;
        font-weight: 800;
        line-height: 1.12;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .idc-name-bn {
        margin-top: .4mm;
        font-size: 6.2pt;
        line-height: 1.2;
        color: #64748b;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .idc-row {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 2mm;
        padding: .55mm 0;
        border-bottom: .2mm dashed #e2e8f0;
        line-height: 1.15;
    }
    .idc-row span {
        flex: 0 0 auto;
        font-size: 4.6pt;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: #94a3b8;
    }
    .idc-row strong {
        min-width: 0;
        font-size: 5.7pt;
        font-weight: 700;
        text-align: right;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .idc-qr {
        position: absolute;
        top: 19.5mm;
        right: 3.2mm;
        width: 15mm;
        text-align: center;
    }
    .idc-qr img { display: block; width: 15mm; height: 15mm; }
    .idc-qr span {
        display: block;
        margin-top: .4mm;
        font-size: 4pt;
        line-height: 1.2;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: .04em;
    }
    .idc-foot {
        position: absolute;
        right: 0;
        bottom: 0;
        left: 0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 3mm;
        height: 6mm;
        padding: 0 3.5mm;
        border-top: .3mm solid #e2e8f0;
        font-size: 4.2pt;
        color: #94a3b8;
        white-space: nowrap;
        overflow: hidden;
    }
    .idc-foot span:first-child {
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .idc-chip {
        flex: 0 0 auto;
        width: 5.5mm;
        height: 3.6mm;
        border-radius: .8mm;
        background: linear-gradient(135deg, #fbbf24, #f59e0b);
        opacity: .85;
    }

    .idc-back-body {
        display: flex;
        gap: 3mm;
        height: 31mm;
        padding: 2.5mm 3.5mm 0;
        overflow: hidden;
    }
    .idc-back-main { flex: 1 1 auto; min-width: 0; }
    .idc-instructions {
        font-size: 5.2pt;
        line-height: 1.4;
        color: #475569;
        white-space: pre-line;
    }
    .idc-contact {
        margin-top: 1.6mm;
        font-size: 4.9pt;
        line-height: 1.4;
        color: #475569;
    }
    .idc-contact div {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .idc-contact span {
        font-weight: 800;
        color: #0f172a;
    }
    .idc-qr-static {
        position: static;
        flex: 0 0 auto;
        width: 15mm;
        padding-top: .5mm;
    }
</style>
