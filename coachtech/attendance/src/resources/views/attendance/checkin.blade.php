<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>勤怠登録_出勤前</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}" >
    <link rel="stylesheet" href="{{ asset('css/common.css') }}" >
    <link rel="stylesheet" href="{{ asset('css/attendance/checkin.css') }}" >
</head>
<body>
    <header class="header">
        <img src="{{ asset('images/CoachTech_White 1.png') }}" alt="coachtech">
        <div class="header-links">
            <a href="{{ route('attendance.show') }}" class="attendance-link">勤怠</a>
            <a href="{{ route('attendance.index') }}" class="attendance-list-link">勤怠一覧</a>
            <a href="" class="request-link">申請</a>
        </div>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
                <button type="submit">ログアウト</button>
        </form>
    </header>
    <main>
        <div class="status">
            <p><span class="status-text">勤務外</span></p>
        </div>
        <div class="date-time">
            <p><span class="date-text" id="current-date"></span></p>
            <p><span class="time-text" id="current-time"></span></p>
        </div>
        <form class="checkin-form" action="{{ route('attendance.checkin') }}" method="POST">
            @csrf
            <button type="submit" class="checkin-button">出勤</button>
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