<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>申請一覧</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}" >
    <link rel="stylesheet" href="{{ asset('css/common.css') }}" >
    <link rel="stylesheet" href="{{ asset('css/admin/requests/index.css') }}" >
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
        <div class="container">
            <h1>申請一覧</h1>

            <!-- タブの切り替え -->
            <ul class="nav nav-tabs">
                <li class="nav-item">
                    <a class="nav-link active" id="pending-tab" data-bs-toggle="tab" href="#pending">承認待ち</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="approved-tab" data-bs-toggle="tab" href="#approved">承認済み</a>
                </li>
            </ul>

            <div class="tab-content">
                <!-- 承認待ちタブ -->
                <div class="tab-pane fade show active" id="pending">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>状態</th>
                                <th>名前</th>
                                <th>対象日時</th>
                                <th>申請理由</th>
                                <th>申請日時</th>
                                <th>詳細</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pendingRequests as $request)
                                <tr>
                                    <td>{{ $request->status }}</td>
                                    <td>{{ $request->user->name }}</td>
                                    <td>{{ \Carbon\Carbon::parse($request->attendance->date)->format('Y年 m月 d日') }}</td>
                                    <td>{{ $request->remarks }}</td>
                                    <td>{{ \Carbon\Carbon::parse($request->created_at)->format('Y年 m月 d日 H:i') }}</td>
                                    <td><a href="{{ route('admin.requests.show', $request->id) }}">詳細</a></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">承認待ちの勤怠はありません。</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- 承認済みタブ -->
                <div class="tab-pane fade" id="approved">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>状態</th>
                                <th>名前</th>
                                <th>対象日時</th>
                                <th>申請理由</th>
                                <th>申請日時</th>
                                <th>詳細</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($approvedRequests as $request)
                                <tr>
                                    <td>{{ $request->status }}</td>
                                    <td>{{ $request->user->name }}</td>
                                    <td>{{ \Carbon\Carbon::parse($request->attendance->date)->format('Y年 m月 d日') }}</td>
                                    <td>{{ $request->remarks }}</td>
                                    <td>{{ \Carbon\Carbon::parse($request->created_at)->format('Y年 m月 d日 H:i') }}</td>
                                    <td><a href="{{ route('admin.show', $request->attendance->id) }}">詳細</a></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">承認済みの勤怠はありません。</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>