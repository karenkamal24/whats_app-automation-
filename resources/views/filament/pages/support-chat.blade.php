<x-filament-panels::page>

<style>
@import url('https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700&display=swap');

:root {
    --green:        #25d366;
    --green-dark:   #128c7e;
    --green-out:    #d9fdd3;
    --teal:         #075e54;
    --bg-chat:      #efeae2;
    --bg-sidebar:   #ffffff;
    --bg-header:    #f0f2f5;
    --bubble-in:    #ffffff;
    --border:       #e9edef;
    --text-dark:    #111b21;
    --text-body:    #3b4a54;
    --text-muted:   #667781;
    --text-faint:   #aebac1;
    --icon-color:   #54656f;
    --font: 'IBM Plex Sans Arabic', sans-serif;
}

/* ── Reset & Base ── */
.chat-page * { font-family: var(--font); box-sizing: border-box; -webkit-font-smoothing: antialiased; }

/* Hide Filament default table chrome completely */
.chat-page .fi-ta-header-toolbar,
.chat-page .fi-ta-filters-form,
.chat-page .fi-input-wrp,
.chat-page .fi-ta-header,
.chat-page .fi-ta-footer,
.chat-page thead,
.chat-page .fi-ta-empty-state-icon,
.chat-page .fi-ta-empty-state-heading,
.chat-page .fi-ta-empty-state-description,
.chat-page .fi-ta-empty-state-actions { display: none !important; }

.chat-page .fi-ta-wrap,
.chat-page .fi-ta-ctn,
.chat-page .fi-ta { background: transparent !important; border: none !important; box-shadow: none !important; }
.chat-page .fi-ta-table { border: none !important; }
.chat-page .fi-ta-table td,
.chat-page .fi-ta-table tr,
.chat-page .fi-ta-row { border: none !important; background: transparent !important; padding: 0 !important; }

/* ── Shell ── */
.chat-page {
    display: flex;
    height: calc(100vh - 80px);
    min-height: 600px;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 6px 40px rgba(0,0,0,.18);
}

/* ════════════════════════════════
   SIDEBAR
════════════════════════════════ */
.sp-sidebar {
    width: 420px;
    flex-shrink: 0;
    display: flex;
    flex-direction: column;
    background: var(--bg-sidebar);
    border-right: 1px solid var(--border);
}

/* Make Filament table rows look like chat items */
.sp-list .fi-ta-table tbody tr td { padding: 0 !important; }
.sp-list .fi-ta-table tbody tr {
    display: flex !important;
    align-items: center !important;
    padding: 12px 16px !important;
    border-bottom: 1px solid var(--border) !important;
    cursor: pointer !important;
    transition: background .12s !important;
    gap: 12px !important;
}
.sp-list .fi-ta-table tbody tr:hover { background: #f5f6f6 !important; }

/* Each cell stretched to fill */
.sp-list .fi-ta-table tbody tr td {
    flex: 1 !important;
    font-size: 14px !important;
    color: var(--text-dark) !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    border: none !important;
}

/* Scrollbar on table container */
.sp-list .fi-ta-ctn { overflow-y: auto !important; height: 100% !important; }
.sp-list .fi-ta-wrap { height: 100% !important; overflow: hidden !important; }

/* Remove horizontal scrollbar */
.sp-list { overflow-x: hidden !important; }

/* Header bar */
.sp-sidebar-header {
    background: var(--bg-header);
    padding: 12px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid var(--border);
    flex-shrink: 0;
}

.sp-brand {
    display: flex;
    align-items: center;
    gap: 11px;
}

.sp-brand-icon {
    width: 40px; height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--teal), var(--green-dark));
    display: flex; align-items: center; justify-content: center;
    font-size: 18px;
    box-shadow: 0 2px 8px rgba(18,140,126,.3);
    flex-shrink: 0;
}

.sp-brand-text h2 {
    font-size: 15px; font-weight: 700;
    color: var(--text-dark); margin: 0 0 1px;
}
.sp-brand-text p {
    font-size: 11px; color: var(--text-muted); margin: 0;
}

.sp-header-actions { display: flex; gap: 4px; }
.sp-icon-btn {
    width: 36px; height: 36px;
    border-radius: 50%; border: none; background: transparent;
    color: var(--icon-color); font-size: 17px;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; transition: background .15s;
}
.sp-icon-btn:hover { background: rgba(0,0,0,.07); }

/* Search */
.sp-search {
    padding: 8px 12px;
    background: var(--bg-sidebar);
    border-bottom: 1px solid var(--border);
    flex-shrink: 0;
}
.sp-search-inner {
    background: var(--bg-header);
    border-radius: 8px;
    padding: 8px 14px;
    display: flex; align-items: center; gap: 8px;
    color: var(--text-muted); font-size: 13px;
}

/* Status filter tabs */
.sp-tabs {
    display: flex;
    padding: 0;
    border-bottom: 1px solid var(--border);
    background: var(--bg-sidebar);
    flex-shrink: 0;
}
.sp-tab {
    flex: 1; padding: 10px 0;
    font-size: 12px; font-weight: 600;
    color: var(--text-muted);
    text-align: center; cursor: pointer;
    border-bottom: 2px solid transparent;
    transition: all .15s;
}
.sp-tab.active {
    color: var(--green-dark);
    border-bottom-color: var(--green-dark);
}

/* List */
.sp-list {
    flex: 1;
    overflow-y: auto;
    scrollbar-width: thin;
    scrollbar-color: #d0d5d8 transparent;
}
.sp-list::-webkit-scrollbar { width: 3px; }
.sp-list::-webkit-scrollbar-thumb { background: #d0d5d8; border-radius: 3px; }

/* ── Conv Item ── */
.conv-item {
    display: flex; align-items: center; gap: 12px;
    padding: 12px 16px;
    border-bottom: 1px solid var(--border);
    cursor: pointer; transition: background .12s;
    position: relative;
}
.conv-item:hover { background: #f5f6f6; }
.conv-item.active { background: #f0f2f5; }

.conv-av {
    width: 48px; height: 48px; border-radius: 50%;
    background: linear-gradient(135deg, #b2dfdb, #80cbc4);
    display: flex; align-items: center; justify-content: center;
    font-size: 21px; flex-shrink: 0; position: relative;
}
.conv-av-dot {
    position: absolute; bottom: 1px; right: 1px;
    width: 12px; height: 12px;
    border-radius: 50%; border: 2px solid #fff;
}
.conv-av-dot.open        { background: var(--green); }
.conv-av-dot.in_progress { background: #f59e0b; }
.conv-av-dot.closed      { background: var(--text-faint); }

.conv-body { flex: 1; min-width: 0; }
.conv-row1 { display: flex; justify-content: space-between; align-items: baseline; }
.conv-name {
    font-size: 14.5px; font-weight: 500; color: var(--text-dark);
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    direction: ltr; flex: 1;
}
.conv-time { font-size: 11px; color: var(--text-muted); flex-shrink: 0; }
.conv-row2 { display: flex; justify-content: space-between; align-items: center; margin-top: 3px; }
.conv-last { font-size: 13px; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; flex: 1; }

.conv-status-tag {
    font-size: 10px; font-weight: 600;
    padding: 2px 7px; border-radius: 10px;
    flex-shrink: 0;
}
.conv-status-tag.open        { background: #e6faf0; color: #059669; }
.conv-status-tag.in_progress { background: #fffbeb; color: #d97706; }
.conv-status-tag.closed      { background: #f3f4f6; color: #6b7280; }

/* ════════════════════════════════
   MAIN
════════════════════════════════ */
.sp-main {
    flex: 1; display: flex; flex-direction: column; min-width: 0;
}

/* Header */
.sp-chat-header {
    background: var(--bg-header);
    padding: 10px 16px;
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center; gap: 12px;
    flex-shrink: 0;
}
.sp-chat-av {
    width: 42px; height: 42px; border-radius: 50%;
    background: linear-gradient(135deg, #b2dfdb, #80cbc4);
    display: flex; align-items: center; justify-content: center;
    font-size: 19px; flex-shrink: 0; cursor: pointer;
}
.sp-chat-info { flex: 1; }
.sp-chat-name { font-size: 15px; font-weight: 600; color: var(--text-dark); direction: ltr; }
.sp-chat-sub  { font-size: 12px; color: var(--text-muted); direction: ltr; margin-top: 1px; }

.sp-status-pill {
    font-size: 11px; font-weight: 600;
    padding: 4px 12px; border-radius: 20px;
    display: flex; align-items: center; gap: 5px;
}
.sp-status-pill::before { content: '●'; font-size: 7px; }
.sp-status-pill.open        { background: #e6faf0; color: #059669; border: 1px solid #a7f3d0; }
.sp-status-pill.in_progress { background: #fffbeb; color: #d97706; border: 1px solid #fde68a; }
.sp-status-pill.closed      { background: #f3f4f6; color: #6b7280; border: 1px solid #e5e7eb; }

.sp-header-btns { display: flex; gap: 2px; }
.sp-hbtn {
    width: 38px; height: 38px; border-radius: 50%;
    border: none; background: transparent;
    color: var(--icon-color); font-size: 18px;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; transition: background .15s;
}
.sp-hbtn:hover { background: rgba(0,0,0,.06); }

/* Messages */
.sp-messages {
    flex: 1; overflow-y: auto;
    padding: 16px 80px;
    display: flex; flex-direction: column; gap: 2px;
    scrollbar-width: thin; scrollbar-color: #c4c9cc transparent;
    background-color: var(--bg-chat);
    background-image: url("data:image/svg+xml,%3Csvg width='52' height='52' viewBox='0 0 52 52' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23c5bfb5' fill-opacity='0.22' fill-rule='evenodd'%3E%3Cpath d='M0 0h4v4H0V0zm48 0h4v4h-4V0zM0 48h4v4H0v-4zm48 48h4v4h-4v-4z'/%3E%3C/g%3E%3C/svg%3E");
}
.sp-messages::-webkit-scrollbar { width: 4px; }
.sp-messages::-webkit-scrollbar-thumb { background: #c4c9cc; border-radius: 4px; }

/* Date badge */
.sp-date {
    display: flex; justify-content: center; margin: 12px 0;
}
.sp-date span {
    background: rgba(225,245,254,.88);
    color: #54656f; font-size: 11.5px; font-weight: 500;
    padding: 4px 12px; border-radius: 7px;
    box-shadow: 0 1px 2px rgba(0,0,0,.09);
}

/* Message rows */
.sp-msg { display: flex; animation: fadeUp .18s ease both; }
@keyframes fadeUp {
    from { opacity: 0; transform: translateY(7px); }
    to   { opacity: 1; transform: translateY(0); }
}
.sp-msg.agent    { justify-content: flex-end; }
.sp-msg.customer { justify-content: flex-start; }
.sp-msg.agent   + .sp-msg.agent    { margin-top: 1px; }
.sp-msg.customer + .sp-msg.customer { margin-top: 1px; }
.sp-msg.agent   + .sp-msg.customer,
.sp-msg.customer + .sp-msg.agent    { margin-top: 10px; }

/* Bubbles */
.sp-bubble {
    max-width: 60%; padding: 7px 10px 5px;
    font-size: 13.5px; line-height: 1.65;
    word-break: break-word; position: relative;
    box-shadow: 0 1px 2px rgba(0,0,0,.12);
}
.sp-bubble p { margin: 0; }

.sp-bubble.agent {
    background: var(--green-out);
    color: var(--text-dark);
    border-radius: 10px 2px 10px 10px;
    margin-right: 2px;
}
.sp-bubble.agent::after {
    content: '';
    position: absolute; top: 0; right: -8px;
    border-left: 8px solid var(--green-out);
    border-top: 8px solid transparent;
}

.sp-bubble.customer {
    background: var(--bubble-in);
    color: var(--text-dark);
    border-radius: 2px 10px 10px 10px;
    margin-left: 2px;
}
.sp-bubble.customer::after {
    content: '';
    position: absolute; top: 0; left: -8px;
    border-right: 8px solid var(--bubble-in);
    border-top: 8px solid transparent;
}

.sp-msg-foot {
    display: flex; align-items: center; justify-content: flex-end;
    gap: 4px; margin-top: 3px;
}
.sp-msg-time { font-size: 10.5px; color: var(--text-muted); }
.sp-ticks { font-size: 12px; color: #53bdeb; }

/* Empty state */
.sp-empty {
    flex: 1; display: flex; flex-direction: column;
    align-items: center; justify-content: center; gap: 14px;
    background: var(--bg-chat);
    background-image: url("data:image/svg+xml,%3Csvg width='52' height='52' viewBox='0 0 52 52' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23c5bfb5' fill-opacity='0.22' fill-rule='evenodd'%3E%3Cpath d='M0 0h4v4H0V0zm48 0h4v4h-4V0zM0 48h4v4H0v-4zm48 48h4v4h-4v-4z'/%3E%3C/g%3E%3C/svg%3E");
}
.sp-empty-icon {
    width: 80px; height: 80px; border-radius: 50%;
    background: rgba(0,0,0,.05);
    display: flex; align-items: center; justify-content: center;
    font-size: 36px;
}
.sp-empty h3 { font-size: 20px; font-weight: 400; color: var(--text-body); margin: 0; }
.sp-empty p  { font-size: 13px; color: var(--text-muted); margin: 0; max-width: 320px; text-align: center; }

/* Input */
.sp-input-area {
    padding: 10px 16px;
    background: var(--bg-header);
    border-top: 1px solid var(--border);
    display: flex; align-items: flex-end; gap: 8px;
    flex-shrink: 0;
}

/* Hide filament label */
.sp-input-area .fi-fo-field-wrp label { display: none !important; }
.sp-input-area .fi-fo-field-wrp { margin: 0 !important; }

.sp-input-area textarea {
    background: #ffffff !important;
    border: none !important;
    border-radius: 22px !important;
    color: var(--text-dark) !important;
    font-size: 14px !important;
    resize: none !important;
    padding: 10px 18px !important;
    line-height: 1.55 !important;
    font-family: var(--font) !important;
    box-shadow: 0 1px 4px rgba(0,0,0,.1) !important;
    min-height: 44px !important;
    max-height: 140px !important;
    overflow-y: auto !important;
    transition: box-shadow .2s !important;
}
.sp-input-area textarea:focus {
    outline: none !important;
    box-shadow: 0 1px 6px rgba(0,0,0,.16) !important;
}
.sp-input-area textarea::placeholder { color: var(--text-faint) !important; }

.sp-attach {
    width: 44px; height: 44px; border-radius: 50%;
    background: transparent; border: none;
    color: var(--icon-color); font-size: 22px;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; flex-shrink: 0; transition: color .15s;
}
.sp-attach:hover { color: var(--green-dark); }

.sp-send {
    width: 50px; height: 50px; border-radius: 50%;
    background: var(--green); border: none; color: #fff;
    font-size: 20px; cursor: pointer; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    transition: background .15s, transform .12s, box-shadow .15s;
    box-shadow: 0 2px 10px rgba(37,211,102,.4);
    align-self: flex-end;
}
.sp-send:hover  { background: #22c55e; transform: scale(1.06); box-shadow: 0 4px 16px rgba(37,211,102,.45); }
.sp-send:active { transform: scale(.93); }
.sp-send:disabled { background: var(--text-faint); box-shadow: none; cursor: not-allowed; transform: none; }
</style>

<div class="chat-page">

    {{-- ══════════ SIDEBAR ══════════ --}}
    <div class="sp-sidebar">

        <div class="sp-sidebar-header">
            <div class="sp-brand">
                <div class="sp-brand-icon">🎧</div>
                <div class="sp-brand-text">
                    <h2>دعم العملاء</h2>
                    <p>المحادثات النشطة</p>
                </div>
            </div>
            <div class="sp-header-actions">
                <button class="sp-icon-btn" title="إعدادات">⚙️</button>
                <button class="sp-icon-btn" title="جديد">✏️</button>
            </div>
        </div>

        <div class="sp-search">
            <div class="sp-search-inner">
                <span>🔍</span>
                <span style="color:#aebac1">بحث في المحادثات…</span>
            </div>
        </div>

        <div class="sp-tabs">
            <div class="sp-tab active">الكل</div>
            <div class="sp-tab">مفتوحة</div>
            <div class="sp-tab">جارية</div>
            <div class="sp-tab">مغلقة</div>
        </div>

        <div class="sp-list">
            {{ $this->table }}
        </div>

    </div>

    {{-- ══════════ MAIN ══════════ --}}
    <div class="sp-main">

        @if($activeConversation)

            <div class="sp-chat-header">
                <div class="sp-chat-av">👤</div>
                <div class="sp-chat-info">
                    <div class="sp-chat-name">
                        {{ $activeConversation->customer_name ?: $activeConversation->customer_phone }}
                    </div>
                    <div class="sp-chat-sub">{{ $activeConversation->customer_phone }}</div>
                </div>
                <div class="sp-status-pill {{ $activeConversation->status }}">
                    {{ match($activeConversation->status) {
                        'open'        => 'مفتوحة',
                        'in_progress' => 'جاري المتابعة',
                        'closed'      => 'مغلقة',
                        default       => $activeConversation->status,
                    } }}
                </div>
                <div class="sp-header-btns">
                    <button class="sp-hbtn" title="بحث">🔍</button>
                    <button class="sp-hbtn" title="خيارات">⋮</button>
                </div>
            </div>

            <div class="sp-messages" id="messages-container">

                <div class="sp-date"><span>اليوم</span></div>

                @forelse($activeConversation->messages as $msg)
                    <div class="sp-msg {{ $msg->sender }}" data-msg-id="{{ $msg->id }}">
                        <div class="sp-bubble {{ $msg->sender }}">
                            <p class="whitespace-pre-wrap">{{ $msg->message }}</p>
                            <div class="sp-msg-foot">
                                <span class="sp-msg-time">{{ $msg->created_at->format('H:i') }}</span>
                                @if($msg->sender === 'agent')
                                    <span class="sp-ticks">✓✓</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div data-empty style="display:flex;align-items:center;justify-content:center;padding-top:80px">
                        <div style="text-align:center;color:#667781">
                            <div style="font-size:40px;margin-bottom:12px">💬</div>
                            <p style="font-size:14px;margin:0">لا توجد رسائل بعد</p>
                        </div>
                    </div>
                @endforelse

            </div>

            <div class="sp-input-area">
                <button class="sp-attach" title="مرفق">📎</button>
                <div style="flex:1">{{ $this->form }}</div>
                <button
                    class="sp-send"
                    wire:click="sendReply"
                    wire:loading.attr="disabled"
                    title="إرسال"
                >
                    <span wire:loading.remove wire:target="sendReply">➤</span>
                    <span wire:loading wire:target="sendReply" style="font-size:13px">…</span>
                </button>
            </div>

        @else

            <div class="sp-empty">
                <div class="sp-empty-icon">💬</div>
                <h3>نظام دعم العملاء</h3>
                <p>اختر محادثة من القائمة على اليمين للبدء في الرد على العملاء</p>
            </div>

        @endif

    </div>
</div>

<script>
function scrollToBottom() {
    const c = document.getElementById('messages-container');
    if (c) c.scrollTop = c.scrollHeight;
}
document.addEventListener('livewire:navigated', scrollToBottom);
document.addEventListener('livewire:updated',   scrollToBottom);
scrollToBottom();

@if($activeConversation)
(function () {
    const conversationId = {{ $activeConversation->id }};
    function appendMessage(data) {
        const c = document.getElementById('messages-container');
        if (!c || c.querySelector(`[data-msg-id="${data.id}"]`)) return;
        const empty = c.querySelector('[data-empty]');
        if (empty) empty.remove();
        const isAgent = data.sender === 'agent';
        const row = document.createElement('div');
        row.className = `sp-msg ${data.sender}`;
        row.setAttribute('data-msg-id', data.id);
        row.innerHTML = `
            <div class="sp-bubble ${data.sender}">
                <p class="whitespace-pre-wrap" style="margin:0">${data.message}</p>
                <div class="sp-msg-foot">
                    <span class="sp-msg-time">${data.created_at}</span>
                    ${isAgent ? '<span class="sp-ticks">✓✓</span>' : ''}
                </div>
            </div>`;
        c.appendChild(row);
        scrollToBottom();
    }
    function subscribe(id) {
        if (typeof window.Echo === 'undefined') { setTimeout(()=>subscribe(id), 500); return; }
        window.Echo.channel(`support.${id}`).listen('message.sent', d => { if(d.sender==='customer') appendMessage(d); });
    }
    document.addEventListener('livewire:updated', () => subscribe(conversationId));
    subscribe(conversationId);
})();
@endif
</script>

</x-filament-panels::page>
