<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>イベント確定通知</title>
</head>
<body style="font-family: sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
        <h1 style="color: #2563eb; margin-top: 0;">イベントが確定しました</h1>
    </div>

    <p>{{ $user->name }} 様</p>

    <p>以下のイベントが確定しました。</p>

    <div style="background-color: #f8f9fa; padding: 15px; border-left: 4px solid #2563eb; margin: 20px 0;">
        <h2 style="margin-top: 0; color: #1e40af;">{{ $event->title }}</h2>

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

    <p>あなたのアサインが確定しました。ダッシュボードで詳細をご確認ください。</p>

    <div style="margin: 30px 0;">
        <a href="{{ route('dashboard') }}" style="display: inline-block; background-color: #2563eb; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold;">
            ダッシュボードを見る
        </a>
    </div>

    <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 30px 0;">

    <p style="color: #6b7280; font-size: 14px;">
        このメールに心当たりがない場合は、破棄していただいて問題ありません。
    </p>
</body>
</html>
