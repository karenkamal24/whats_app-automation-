<x-filament-panels::page>

<style>
    @import url('https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700&display=swap');

    .chat-wrapper * {
        font-family: 'IBM Plex Sans Arabic', sans-serif;
    }

    /* ── Sidebar ── */
    .chat-sidebar {
        width: 320px;
        min-width: 280px;
        border-left: 1px solid #e5e7eb;
        background: #f9fafb;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .dark .chat-sidebar {
        background: #111827;
        border-color: #1f2937;
    }

    .sidebar-header {
        padding: 18px 20px;
        background: #fff;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .dark .sidebar-header {
        background: #0f172a;
        border-color: #1e293b;
    }

    .sidebar-header-icon {
        width: 34px;
        height: 34px;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
    }

    .sidebar-header h2 {
        font-size: 15px;
        font-weight: 700;
        color: #111827;
        letter-spacing: -0.02em;
    }

    .dark .sidebar-header h2 { color: #f1f5f9; }

    .sidebar-scroll {
        overflow-y: auto;
        flex: 1;
    }

    .sidebar-scroll::-webkit-scrollbar { width: 4px; }
    .sidebar-scroll::-webkit-scrollbar-track { background: transparent; }
    .sidebar-scroll::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 99px; }

    /* ── Chat Area ── */
    .chat-main {
        flex: 1;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        background: #fff;
    }

    .dark .chat-main { background: #0f172a; }

    /* Header */
    .chat-header {
        padding: 14px 20px;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #fff;
        gap: 12px;
    }

    .dark .chat-header {
        background: #0f172a;
        border-color: #1e293b;
    }

    .chat-header-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #6366f1 0%, #a78bfa 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        font-weight: 700;
        color: white;
        flex-shrink: 0;
        letter-spacing: -0.02em;
    }

    .chat-header-info { flex: 1; min-width: 0; }

    .chat-header-name {
        font-size: 15px;
        font-weight: 700;
        color: #111827;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .dark .chat-header-name { color: #f1f5f9; }

    .chat-header-phone {
        font-size: 12px;
        color: #9ca3af;
        margin-top: 1px;
        direction: ltr;
        text-align: right;
    }

    /* Status badges */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 12px;
        border-radius: 99px;
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap;
        flex-shrink: 0;
    }

    .status-open    { background: #dcfce7; color: #16a34a; }
    .status-progress{ background: #fef9c3; color: #ca8a04; }
    .status-closed  { background: #f1f5f9; color: #64748b; }

    /* Messages area */
    .messages-area {
        flex: 1;
        overflow-y: auto;
        padding: 20px;
        display: flex;
        flex-direction: column;
        gap: 10px;
        background: #f8fafc;
        scroll-behavior: smooth;
    }

    .dark .messages-area { background: #0d1117; }

    .messages-area::-webkit-scrollbar { width: 4px; }
    .messages-area::-webkit-scrollbar-track { background: transparent; }
    .messages-area::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 99px; }

    /* Message bubbles */
    .msg-row {
        display: flex;
        align-items: flex-end;
        gap: 8px;
        animation: fadeUp 0.2s ease both;
    }

    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(6px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .msg-row.agent  { flex-direction: row-reverse; }
    .msg-row.customer { flex-direction: row; }

    .msg-avatar {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 700;
    }

    .msg-avatar.agent-av    { background: linear-gradient(135deg,#6366f1,#a78bfa); color:#fff; }
    .msg-avatar.customer-av { background: linear-gradient(135deg,#e2e8f0,#cbd5e1); color:#475569; }

    .msg-bubble {
        max-width: 62%;
        padding: 10px 14px;
        font-size: 13.5px;
        line-height: 1.65;
        position: relative;
        word-break: break-word;
    }

    .msg-bubble.agent {
        background: linear-gradient(135deg, #6366f1 0%, #7c3aed 100%);
        color: #fff;
        border-radius: 18px 18px 4px 18px;
        box-shadow: 0 2px 12px rgba(99,102,241,.25);
    }

    .msg-bubble.customer {
        background: #fff;
        color: #1e293b;
        border: 1px solid #e2e8f0;
        border-radius: 18px 18px 18px 4px;
        box-shadow: 0 1px 4px rgba(0,0,0,.06);
    }

    .dark .msg-bubble.customer {
        background: #1e293b;
        color: #e2e8f0;
        border-color: #334155;
    }

    .msg-meta {
        font-size: 10px;
        margin-top: 4px;
        opacity: 0.65;
        text-align: left;
        direction: ltr;
    }

    .msg-bubble.agent  .msg-meta { color: rgba(255,255,255,.8); }
    .msg-bubble.customer .msg-meta { color: #94a3b8; }

    /* Input area */
    .input-area {
        padding: 14px 18px;
        border-top: 1px solid #e5e7eb;
        background: #fff;
        display: flex;
        align-items: flex-end;
        gap: 10px;
    }

    .dark .input-area {
        background: #0f172a;
        border-color: #1e293b;
    }

    .input-wrapper { flex: 1; }

    .send-btn {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        background: linear-gradient(135deg, #6366f1, #7c3aed);
        color: white;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: opacity .15s, transform .15s, box-shadow .15s;
        box-shadow: 0 2px 10px rgba(99,102,241,.35);
        font-size: 18px;
    }

    .send-btn:hover:not(:disabled) {
        opacity: .9;
        transform: scale(1.04);
        box-shadow: 0 4px 16px rgba(99,102,241,.45);
    }

    .send-btn:disabled { opacity: .5; cursor: not-allowed; transform: none; }

    /* Empty state */
    .empty-state {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .empty-card {
        text-align: center;
        padding: 40px;
    }

    .empty-icon {
        width: 64px;
        height: 64px;
        background: linear-gradient(135deg,#ede9fe,#c7d2fe);
        border-radius: 20px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        margin-bottom: 16px;
    }

    .empty-title {
        font-size: 15px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 6px;
    }

    .dark .empty-title { color: #94a3b8; }

    .empty-sub {
        font-size: 13px;
        color: #9ca3af;
    }

    /* Date divider */
    .date-divider {
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 8px 0;
    }

    .date-divider::before,
    .date-divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: #e2e8f0;
    }

    .dark .date-divider::before,
    .dark .date-divider::after { background: #1e293b; }

    .date-divider span {
        font-size: 11px;
        color: #94a3b8;
        white-space: nowrap;
        padding: 2px 10px;
        background: #f1f5f9;
        border-radius: 99px;
    }

    .dark .date-divider span { background: #1e293b; }

    /* Livewire loading spinner */
    .loading-dots span {
        display: inline-block;
        width: 5px; height: 5px;
        border-radius: 50%;
        background: rgba(255,255,255,.8);
        animation: dot 1.2s infinite;
        margin: 0 2px;
    }
    .loading-dots span:nth-child(2) { animation-delay: .2s; }
    .loading-dots span:nth-child(3) { animation-delay: .4s; }
    @keyframes dot { 0%,80%,100%{transform:scale(.6);opacity:.4} 40%{transform:scale(1);opacity:1} }
</style>

<div class="chat-wrapper" dir="rtl">
    <div style="display:flex; height:80vh; overflow:hidden; border-radius:16px; border:1px solid #e5e7eb; box-shadow:0 4px 24px rgba(0,0,0,.08);">

        {{-- ═══ Sidebar ═══ --}}
        <div class="chat-sidebar">
            <div class="sidebar-header">
                <div class="sidebar-header-icon">💬</div>
                <h2>المحادثات</h2>
            </div>
            <div class="sidebar-scroll">
                {{ $this->table }}
            </div>
        </div>

        {{-- ═══ Main Chat ═══ --}}
        <div class="chat-main">

            @if($activeConversation)

                {{-- Header --}}
                <div class="chat-header">
                    <div class="chat-header-avatar">
                        {{ mb_substr($activeConversation->customer_name ?: $activeConversation->customer_phone, 0, 1) }}
                    </div>
                    <div class="chat-header-info">
                        <div class="chat-header-name">
                            {{ $activeConversation->customer_name ?: $activeConversation->customer_phone }}
                        </div>
                        <div class="chat-header-phone">{{ $activeConversation->customer_phone }}</div>
                    </div>
                    <span class="status-badge
                        {{ $activeConversation->status === 'open'        ? 'status-open'     : '' }}
                        {{ $activeConversation->status === 'in_progress' ? 'status-progress' : '' }}
                        {{ $activeConversation->status === 'closed'      ? 'status-closed'   : '' }}">
                        {{ match($activeConversation->status) {
                            'open'        => '● مفتوحة',
                            'in_progress' => '● جاري',
                            'closed'      => '● مغلقة',
                            default       => $activeConversation->status,
                        } }}
                    </span>
                </div>

                {{-- Messages --}}
                <div id="messages-container" class="messages-area">

                    @forelse($activeConversation->messages as $msg)
                        <div class="msg-row {{ $msg->sender }}" data-msg-id="{{ $msg->id }}">
                            <div class="msg-avatar {{ $msg->sender === 'agent' ? 'agent-av' : 'customer-av' }}">
                                {{ $msg->sender === 'agent' ? '👤' : '🙂' }}
                            </div>
                            <div class="msg-bubble {{ $msg->sender }}">
                                <p class="whitespace-pre-wrap" style="margin:0">{{ $msg->message }}</p>
                                <div class="msg-meta">
                                    {{ $msg->created_at->format('H:i') }}
                                    @if($msg->sender === 'agent') · موظف @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div data-empty style="margin:auto; text-align:center; color:#9ca3af; font-size:13px; padding-top:40px;">
                            لا توجد رسائل بعد
                        </div>
                    @endforelse

                </div>

                {{-- Input --}}
                <div class="input-area">
                    <div class="input-wrapper">
                        {{ $this->form }}
                    </div>
                    <button
                        wire:click="sendReply"
                        wire:loading.attr="disabled"
                        class="send-btn"
                        title="إرسال"
                    >
                        <span wire:loading.remove wire:target="sendReply">➤</span>
                        <span wire:loading wire:target="sendReply" class="loading-dots">
                            <span></span><span></span><span></span>
                        </span>
                    </button>
                </div>

            @else

                <div class="empty-state">
                    <div class="empty-card">
                        <div class="empty-icon">💬</div>
                        <div class="empty-title">لا توجد محادثة محددة</div>
                        <div class="empty-sub">اختر محادثة من القائمة للبدء</div>
                    </div>
                </div>

            @endif

        </div>
    </div>
</div>


<script>
    /* ── Scroll ── */
    function scrollToBottom() {
        const c = document.getElementById('messages-container');
        if (c) c.scrollTop = c.scrollHeight;
    }
    document.addEventListener('livewire:navigated', scrollToBottom);
    document.addEventListener('livewire:updated',   scrollToBottom);
    scrollToBottom();

    /* ── Real-time via Echo ── */
    @if($activeConversation)
    (function () {
        const conversationId = {{ $activeConversation->id }};

        function appendMessage(data) {
            const container = document.getElementById('messages-container');
            if (!container) return;
            if (container.querySelector(`[data-msg-id="${data.id}"]`)) return;

            const empty = container.querySelector('[data-empty]');
            if (empty) empty.remove();

            const isAgent = data.sender === 'agent';
            const row = document.createElement('div');
            row.className = `msg-row ${isAgent ? 'agent' : 'customer'}`;
            row.setAttribute('data-msg-id', data.id);
            row.innerHTML = `
                <div class="msg-avatar ${isAgent ? 'agent-av' : 'customer-av'}">
                    ${isAgent ? '👤' : '🙂'}
                </div>
                <div class="msg-bubble ${isAgent ? 'agent' : 'customer'}">
                    <p class="whitespace-pre-wrap" style="margin:0">${data.message}</p>
                    <div class="msg-meta">
                        ${data.created_at}${isAgent ? ' · موظف' : ''}
                    </div>
                </div>`;
            container.appendChild(row);
            scrollToBottom();
        }

        function subscribe(id) {
            if (typeof window.Echo === 'undefined') {
                setTimeout(() => subscribe(id), 500);
                return;
            }
            window.Echo.channel(`support.${id}`)
                .listen('message.sent', (data) => {
                    if (data.sender === 'customer') appendMessage(data);
                });
        }

        document.addEventListener('livewire:updated', () => subscribe(conversationId));
        subscribe(conversationId);
    })();
    @endif
</script>

</x-filament-panels::page>
