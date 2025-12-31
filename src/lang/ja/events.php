<?php

return [
    // Page Titles
    'management' => 'イベント管理',
    'create_new' => '新しいイベントを作成',
    'create_event' => 'イベント作成',
    'edit_event' => 'イベント編集',
    'event_details' => 'イベント詳細',
    'create_assignments' => 'アサイン作成',

    // Table Headers
    'title' => 'タイトル',
    'date' => '日付',
    'time' => '時間',
    'status' => 'ステータス',
    'applications' => '申込',
    'actions' => '操作',

    // Status
    'draft' => '下書き',
    'open' => '募集中',
    'closed' => '終了',
    'completed' => '完了',

    // Tags
    'recurring' => '繰り返し',
    'template' => 'テンプレート',

    // Actions
    'view' => '表示',
    'edit' => '編集',
    'assign' => 'アサイン',
    'delete' => '削除',
    'cancel' => 'キャンセル',
    'save' => '保存',
    'save_assignments' => 'アサインを保存',

    // Messages
    'no_events' => 'イベントがありません',
    'get_started' => '新しいイベントを作成して始めましょう',
    'delete_confirm' => 'このイベントを削除してもよろしいですか？',
    'success' => '成功しました',

    // Assignment
    'assign_users' => 'ユーザーをアサイン',
    'participants' => '参加者',
    'leader' => 'リーダー',
    'select_participants' => '参加者を選択 (2名)',
    'select_leader' => 'リーダーを選択 (任意、1名)',
    'click_to_assign' => 'クリックしてアサイン',
    'assignments_created' => '件のアサインを作成',
    'none' => 'なし',

    // Event Details
    'locations' => '場所',
    'not_set' => '未設定',
    'view_details' => '詳細を表示',
    'edit_event_btn' => 'イベントを編集',
    'assign_users_btn' => 'ユーザーをアサイン',
    'delete_event' => 'イベントを削除',

    // Search
    'search' => '検索',
    'search_events' => 'イベントを検索',
    'location' => '場所',
    'event_date' => 'イベント日',
    'clear_filters' => 'フィルタをクリア',
    'all_statuses' => 'すべてのステータス',

    // Form Fields
    'use_template' => 'テンプレートを使用（任意）',
    'select_template' => '-- テンプレートを選択 --',
    'start_time' => '開始時刻',
    'end_time' => '終了時刻',
    'assignment_slot_duration' => 'アサインスロット時間（分）',
    'assignment_slot_duration_help' => '管理者がユーザーをタイムスロットに割り当てるための時間',
    'application_slot_duration' => '申込スロット時間（分）',
    'application_slot_duration_help' => 'ユーザーが可否を申請する時間枠',
    'minutes' => '分',
    'hour' => '時間',
    'hours' => '時間',
    'locations_for_assignment' => 'アサイン用の場所（2-3箇所）',
    'locations_help' => 'このイベントで2-3箇所の場所名を入力してください（例：北西、北東）',
    'notes' => '備考',
    'repeat_weekly' => '毎週繰り返す',
    'repeat_until' => '繰り返し終了日',
    'save_as_template' => '将来の使用のためにテンプレートとして保存',
    'create_event_button' => 'イベントを作成',

    // Show Page
    'event_information' => 'イベント情報',
    'back_to_list' => '一覧に戻る',
    'slot_duration' => 'スロット時間',
    'time_slots' => 'タイムスロット',
    'recurrence' => '繰り返し',
    'weekly_until' => 'まで毎週',
    'recurring_instances' => '件の繰り返しインスタンスを作成済み',
    'template_event' => 'テンプレートイベント',
    'no_time_slots' => 'タイムスロットがありません',
    'capacity' => '定員',
    'full' => '満員',
    'available' => '空きあり',
    'assigned' => 'アサイン済み',
    'no_applications' => 'まだ申込がありません',
    'user' => 'ユーザー',
    'availability' => '可否',
    'available_status' => '可',
    'unavailable_status' => '不可',
    'comment' => 'コメント',
    'applied_at' => '申込日時',

    // Edit Page
    'note_assigned_slots' => 'このイベントには既にアサインされたタイムスロットがあります。時間とスロット時間は変更できません。',
    'note_recurring_event' => 'これは繰り返しイベントです。変更はこのイベントのみに適用され、繰り返しインスタンスには影響しません。',
    'update_event' => 'イベントを更新',

    // Assignments Page
    'create_assignments_title' => 'アサイン作成',
    'back_to_events' => '← イベント一覧に戻る',
    'event_details' => 'イベント詳細',
    'assign_users_to_slots' => 'タイムスロット × 場所にユーザーをアサイン',
    'assignment_instruction' => 'セルをクリックしてユーザーを選択してください。各場所は通常、参加者2名 + リーダー1名（任意）が必要です。',
    'time' => '時間',
    'assignments' => 'アサイン',
    'participants_and_leader' => '参加者 (2) + リーダー (1)',
    'click_to_assign' => 'クリックしてアサイン',
    'no_slot' => 'スロットなし',
    'no_slot_found' => 'スロットが見つかりません。イベントを編集してスロットを再生成してください。',
    'no_slot_for_location' => 'のスロットが見つかりません。イベントを編集してスロットを再生成してください。',
    'assignments_created' => '件のアサインを作成',
    'assign_users_modal_title' => 'ユーザーをアサイン',
    'select_participants_2' => '参加者を選択 (2名)',
    'select_leader_optional' => 'リーダーを選択 (任意、1名)',
    'invalid_slot' => '無効なスロット - このセルのスロットIDが見つかりません',
];
