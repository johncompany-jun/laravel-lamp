<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>イベント中止通知</title>
</head>
<body style="font-family: sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #fef2f2; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
        <h1 style="color: #dc2626; margin-top: 0;">イベントが中止になりました</h1>
    </div>

    <p>{{ $user->name }} 様</p>

    <p>申し訳ございませんが、以下のイベントが中止になりました。</p>

    <div style="background-color: #fef2f2; padding: 15px; border-left: 4px solid #dc2626; margin: 20px 0;">
        <h2 style="margin-top: 0; color: #991b1b;">{{ $event->title }}</h2>

        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; font-weight: bold; width: 100px;">日付:</td>
                <td style="padding: 8px 0;">{{ $event->event_date->format('Y年m月d日') }} ({{ ['日', '月', '火', '水', '木', '金', '土'][$event->event_date->dayOfWeek] }})</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; font-weight: bold;">時間:</td>
                <td style="padding: 8px 0;">{{ date('H:i', strtotime($event->start_time)) }} - {{ date('H:i', strtotime($event->end_time)) }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; font-weight: bold;">場所:</td>
                <td style="padding: 8px 0;">{{ $event->location }}</td>
            </tr>
        </table>
    </div>

    <p>このイベントへの申し込みは自動的にキャンセルされました。</p>

    @if ($event->notes)
    <div style="background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
        <h3 style="margin-top: 0; font-size: 16px;">備考:</h3>
        <p style="margin-bottom: 0;">{{ $event->notes }}</p>
    </div>
    @endif

    <div style="margin: 30px 0;">
        <a href="{{ route('dashboard') }}" style="display: inline-block; background-color: #dc2626; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold;">
            ダッシュボードを見る
        </a>
    </div>

    <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 30px 0;">

    <p style="color: #6b7280; font-size: 14px;">
        このメールに心当たりがない場合は、破棄していただいて問題ありません。
    </p>
</body>
</html>
