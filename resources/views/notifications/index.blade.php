<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div><p class="text-xs font-semibold uppercase tracking-[0.16em] text-amber-700">Pembaruan</p><h2 class="mt-1 text-2xl font-bold text-slate-900">Notifikasi</h2></div>
            @if(auth()->user()->unreadNotifications->isNotEmpty())
                <form action="{{ route('notifications.read-all') }}" method="POST">@csrf<button class="text-sm font-semibold text-sky-800 hover:text-sky-950">Tandai semua dibaca</button></form>
            @endif
        </div>
    </x-slot>

    <div class="py-8"><div class="mx-auto max-w-4xl space-y-3 px-4 sm:px-6 lg:px-8">
        @forelse($notifications as $notification)
            <form action="{{ route('notifications.read', $notification->id) }}" method="POST">@csrf
                <button class="w-full rounded-xl border p-4 text-left transition hover:border-sky-300 hover:bg-sky-50 {{ $notification->read_at ? 'border-slate-200 bg-white' : 'border-sky-200 bg-sky-50/60' }}">
                    <span class="flex items-start gap-3"><span class="mt-1 h-2 w-2 shrink-0 rounded-full {{ $notification->read_at ? 'bg-slate-300' : 'bg-sky-600' }}"></span><span><span class="block font-semibold text-slate-900">{{ $notification->data['title'] ?? 'Pemberitahuan' }}</span><span class="mt-1 block text-sm text-slate-600">{{ $notification->data['message'] ?? '' }}</span><span class="mt-2 block text-xs text-slate-400">{{ $notification->created_at->format('d/m/Y H:i') }}</span></span></span>
                </button>
            </form>
        @empty
            <p class="rounded-xl border border-dashed border-slate-300 bg-white p-8 text-center text-sm text-slate-500">Belum ada notifikasi.</p>
        @endforelse
        {{ $notifications->links() }}
    </div></div>
</x-app-layout>