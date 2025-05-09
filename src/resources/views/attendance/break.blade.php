<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>勤怠登録_休憩中</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}" >
    <link rel="stylesheet" href="{{ asset('css/common.css') }}" >
    <link rel="stylesheet" href="{{ asset('css/attendance/break.css') }}" >
</head>
<body>
    <header class="header">
        <img src="{{ asset('images/CoachTech_White 1.png') }}" alt="coachtech">
        <div class="header-links">
            <a href="{{ route('attendance.show') }}" class="attendance-link">勤怠</a>
            <a href="{{ route('attendance.index') }}" class="attendance-list-link">勤怠一覧</a>
            <a href="{{ route('requests.index') }}" class="request-link">申請</a>
        </div>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
                <button type="submit">ログアウト</button>
        </form>
    </header>
    <main>
        <div class="status">
            <p><span class="status-text">休憩中</span></p>
        </div>
        @php
            use Carbon\Carbon;
            $now = Carbon::now()->locale('ja');
            $date = $now->isoFormat('YYYY年M月D日(ddd)');
            $time = $now->format('H:i');
        @endphp

        <div class="date-time">
            <p><span class="date-text">{{ $date }}</span></p>
            <p><span class="time-text">{{ $time }}</span></p>
        </div>
        <form class="break-form" action="{{ route('attendance.break.end') }}" method="POST">
            @csrf
            <button class="break-button" type="submit">休憩戻</button>
        </form>
    </main>

    <script>
        function updateTime() {
            const now = new Date();

            const weekdays = ["日", "月", "火", "水", "木", "金", "土"];
            const formattedDate = now.getFullYear() + "年" +
                (now.getMonth() + 1) + "月" +
                now.getDate() + "日 (" +
                weekdays[now.getDay()] + ")";

            const formattedTime = ("0" + now.getHours()).slice(-2) + ":" +
                ("0" + now.getMinutes()).slice(-2);

            document.getElementById("current-date").innerText = formattedDate;
            document.getElementById("current-time").innerText = formattedTime;
        }

        setInterval(updateTime, 1000);
        updateTime();
    </script>
</body>
</html>