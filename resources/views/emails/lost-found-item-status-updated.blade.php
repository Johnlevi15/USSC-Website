@php
    $statusLabels = [
        'lost' => 'Lost',
        'found' => 'Found',
        'claimed' => 'Claimed',
    ];
    $approvalLabels = [
        'pending' => 'Pending Review',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
    ];
    $oldStatusLabel = $statusLabels[$oldStatus] ?? ucfirst($oldStatus);
    $newStatusLabel = $statusLabels[$item->status] ?? ucfirst($item->status);
    $approvalLabel = $approvalLabels[$item->approval_status] ?? ucfirst($item->approval_status);
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lost & Found Update</title>
</head>
<body style="margin:0;background:#f3f4f6;font-family:Arial,sans-serif;color:#1f2937;">
    <div style="max-width:640px;margin:0 auto;padding:28px 16px;">
        <div style="background:#ffffff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;">
            <div style="background:#450a0a;color:#ffffff;padding:18px 24px;">
                <table role="presentation" style="width:100%;border-collapse:collapse;">
                    <tr>
                        <td style="width:52px;padding:0 14px 0 0;vertical-align:middle;">
                            <img src="{{ asset('logo1.png') }}" alt="USSC Logo" width="42" height="42" style="display:block;width:42px;height:42px;object-fit:contain;">
                        </td>
                        <td style="padding:0;vertical-align:middle;">
                            <h1 style="margin:0;font-size:20px;">USSC Lost & Found Update</h1>
                        </td>
                    </tr>
                </table>
            </div>
            <div style="padding:24px;line-height:1.6;">
                <p style="margin-top:0;">Hello {{ $item->poster?->name ?? 'Student' }},</p>
                <p>Your reported lost-and-found item has been updated.</p>

                <table style="width:100%;border-collapse:collapse;margin:18px 0;">
                    <tr>
                        <td style="padding:8px 0;color:#6b7280;width:150px;">Item</td>
                        <td style="padding:8px 0;font-weight:bold;">{{ $item->item_name }}</td>
                    </tr>
                    <tr>
                        <td style="padding:8px 0;color:#6b7280;">Previous status</td>
                        <td style="padding:8px 0;">{{ $oldStatusLabel }}</td>
                    </tr>
                    <tr>
                        <td style="padding:8px 0;color:#6b7280;">Current status</td>
                        <td style="padding:8px 0;font-weight:bold;">{{ $newStatusLabel }}</td>
                    </tr>
                    <tr>
                        <td style="padding:8px 0;color:#6b7280;">Approval status</td>
                        <td style="padding:8px 0;">{{ $approvalLabel }}</td>
                    </tr>
                    <tr>
                        <td style="padding:8px 0;color:#6b7280;">Place</td>
                        <td style="padding:8px 0;">{{ $item->place ?? 'Not specified' }}</td>
                    </tr>
                </table>

                @if(filled($item->admin_remarks))
                    <div style="border-left:4px solid #991b1b;background:#fef2f2;padding:12px 14px;margin:18px 0;">
                        <strong>Admin remarks</strong>
                        <div style="margin-top:6px;white-space:pre-line;">{{ $item->admin_remarks }}</div>
                    </div>
                @endif

                <p>Please contact or visit the USSC office if you need help with this item.</p>
                <p style="margin-bottom:0;">Thank you,<br>USSC Administration</p>
            </div>
        </div>
    </div>
</body>
</html>
