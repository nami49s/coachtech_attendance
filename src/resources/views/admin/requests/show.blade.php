<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>申請承認画面</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}" >
    <link rel="stylesheet" href="{{ asset('css/common.css') }}" >
    <link rel="stylesheet" href="{{ asset('css/admin/requests/show.css') }}" >
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <header class="header">
        <img src="{{ asset('images/CoachTech_White 1.png') }}" alt="coachtech">
        <div class="header-links">
            <a href="{{ route('admin.index') }}" class="attendance-link">勤怠一覧</a>
            <a href="{{ route('admin.staffs') }}" class="attendance-list-link">スタッフ一覧</a>
            <a href="{{ route('admin.requests.index') }}" class="request-link">申請一覧</a>
        </div>
        <form action="{{ route('admin.logout') }}" method="POST">
            @csrf
                <button type="submit">ログアウト</button>
        </form>
    </header>
    <main>
        <h1><span class="line">⎜ </span>勤怠詳細</h1>

        <div class="attendance-detail">
            <table class="detail-table">
                <tr>
                    <th>名前</th>
                    <td>{{ $attendanceRequest->user->name }}</td>
                </tr>
                <tr>
                    <th>日付</th>
                    <td>
                        <div class="date-container">
                            <span>{{ \Carbon\Carbon::parse($attendanceRequest->attendance->date)->format('Y年') }}</span>
                            <span class="date-divider"></span>
                            <span>{{ \Carbon\Carbon::parse($attendanceRequest->attendance->date)->format('m月 d日') }}</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>出勤・退勤</th>
                    <td>
                        <span>
                            <span>{{ \Carbon\Carbon::parse($attendanceRequest->checkin_time)->format('H:i') }}</span>
                            <span>〜</span>
                            <span>{{ \Carbon\Carbon::parse($attendanceRequest->checkout_time)->format('H:i') }}</span>
                        </span>
                    </td>
                </tr>
                @foreach ($attendanceRequest->breakRequests as $index => $breakRequest)
                <tr>
                    <th>休憩{{ $index + 1 }}</th>
                    <td>
                        <span>
                            <span>{{ \Carbon\Carbon::parse($breakRequest->break_start)->format('H:i') }}</span>
                            <span>〜</span>
                            <span>{{ \Carbon\Carbon::parse($breakRequest->break_end)->format('H:i') }}</span>
                        </span>
                    </td>
                </tr>
                @endforeach
                <tr>
                    <th>備考</th>
                    <td>{{ $attendanceRequest->remarks }}</td>
                </tr>
            </table>
        </div>
        <form id="approve-form" action="{{ route('admin.requests.approve', $attendanceRequest->id) }}" method="POST">
            @csrf
            <div style="text-align: right;">
                <button type="submit" id="approve-btn" class="btn btn-primary">承認</button>
            </div>
        </form>
    </main>
    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $('#approve-form').on('submit', function(event) {
                event.preventDefault(); // デフォルトのフォーム送信を防ぐ

                // ボタンを一時的に無効化
                $('#approve-btn').prop('disabled', true).text('承認中...');

                // AJAXリクエストを送信
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: $(this).serialize(), // フォームのデータを送信
                    success: function(response) {
                        // 承認が成功した場合
                        if(response.success) {
                            // ボタンのテキストを変更
                            $('#approve-btn').prop('disabled', true).text('承認済み').addClass('approved');
                            // ボタンを無効化する
                        } else {
                            // 成功しなかった場合（エラーメッセージなど）
                            alert('申請の承認に失敗しました。');
                            $('#approve-btn').prop('disabled', false).text('承認');
                        }
                    },
                    error: function(xhr, status, error) {
                        // エラーハンドリング（必要に応じて）
                        alert('エラーが発生しました。再度試してください。');
                        $('#approve-btn').prop('disabled', false).text('承認');
                    }
                });
            });
        });
    </script>
</body>
</html>
