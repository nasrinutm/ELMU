<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User List</title>
    @vite('resources/css/app.css') {{-- only if you're using Tailwind via Vite --}}
</head>
<body class="p-6 bg-gray-100">

    <h1 class="text-2xl font-bold mb-4">User List</h1>

    @foreach ($users as $user)
        @php
            $color = match ($user->role) {
                'admin' => 'bg-red-500',
                'teacher' => 'bg-green-500',
                'student' => 'bg-blue-500',
                default => 'bg-gray-400',
            };
        @endphp

        <div class="p-3 mb-2 text-white rounded {{ $color }}">
            {{ $user->name }} — {{ ucfirst($user->role) }}
        </div>
    @endforeach

</body>
</html>
