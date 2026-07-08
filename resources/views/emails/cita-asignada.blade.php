<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><title>Confirmación de cita</title></head>
<body style="margin:0;background:#f1f5f9;font-family:Arial,sans-serif;color:#0f172a;">
    <div style="max-width:620px;margin:0 auto;padding:32px 16px;">
        <div style="overflow:hidden;border-radius:18px;background:#ffffff;box-shadow:0 8px 24px rgba(15,23,42,.08);">
            <div style="background:#1d4ed8;padding:28px;color:#ffffff;"><div style="font-size:22px;font-weight:700;">LexBot AI</div><div style="margin-top:6px;font-size:14px;color:#dbeafe;">Confirmación de cita jurídica</div></div>
            <div style="padding:28px;"><h1 style="margin:0 0 14px;font-size:22px;">Hola, {{ $cliente }}</h1><p style="line-height:1.6;color:#475569;">Tu caso fue registrado correctamente y se asignó una cita para continuar el seguimiento.</p><table style="width:100%;margin:22px 0;border-collapse:collapse;"><tr><td style="padding:12px;border-bottom:1px solid #e2e8f0;color:#64748b;">Abogado</td><td style="padding:12px;border-bottom:1px solid #e2e8f0;font-weight:700;">{{ $abogado }}</td></tr><tr><td style="padding:12px;border-bottom:1px solid #e2e8f0;color:#64748b;">Especialidad</td><td style="padding:12px;border-bottom:1px solid #e2e8f0;font-weight:700;">{{ $especialidad }}</td></tr><tr><td style="padding:12px;border-bottom:1px solid #e2e8f0;color:#64748b;">Fecha</td><td style="padding:12px;border-bottom:1px solid #e2e8f0;font-weight:700;">{{ $fecha }}</td></tr><tr><td style="padding:12px;color:#64748b;">Hora</td><td style="padding:12px;font-weight:700;">{{ substr($hora, 0, 5) }}</td></tr></table><p style="line-height:1.6;color:#475569;">También podrás revisar los cambios en el portal y, si vinculaste Telegram con este correo, recibirás avisos en el bot.</p></div>
        </div>
    </div>
</body>
</html>
