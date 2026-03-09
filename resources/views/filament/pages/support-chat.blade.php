<x-filament-panels::page>

    <div class="flex h-[80vh] overflow-hidden rounded-xl border bg-white dark:bg-gray-900">

        {{-- Sidebar --}}
        <div class="w-1/3 border-r bg-gray-50 dark:bg-gray-800 overflow-y-auto">
            <div class="p-4 font-bold border-b dark:border-gray-700 dark:text-white">
                المحادثات
            </div>
            {{ $this->table }}
        </div>

        {{-- Chat Area --}}
        <div class="flex-1 flex flex-col overflow-hidden">

            @if($activeConversation)

                {{-- Header --}}
                <div class="p-4 border-b dark:border-gray-700 flex justify-between items-center bg-white dark:bg-gray-900">
                    <div>
                        <div class="font-bold dark:text-white">
                            {{ $activeConversation->customer_name ?: $activeConversation->customer_phone }}
                        </div>
                        <div class="text-xs text-gray-500">
                            {{ $activeConversation->customer_phone }}
                        </div>
                    </div>
                    <span class="text-xs px-2 py-1 rounded-full
                        {{ $activeConversation->status === 'open'        ? 'bg-green-100 text-green-700'  : '' }}
                        {{ $activeConversation->status === 'in_progress' ? 'bg-yellow-100 text-yellow-700': '' }}
                        {{ $activeConversation->status === 'closed'      ? 'bg-gray-100 text-gray-500'    : '' }}">
                        {{ match($activeConversation->status) {
                            'open'        => '🟢 مفتوحة',
                            'in_progress' => '🟡 جاري المتابعة',
                            'closed'      => '⚫ مغلقة',
                            default       => $activeConversation->status,
                        } }}
                    </span>
                </div>

                {{-- Messages --}}
                <div
                    id="messages-container"
                    class="flex-1 overflow-y-auto p-4 space-y-3 bg-gray-50 dark:bg-gray-800"
                >
                    @forelse($activeConversation->messages as $msg)
                        <div class="flex {{ $msg->sender === 'agent' ? 'justify-end' : 'justify-start' }}"
                             data-msg-id="{{ $msg->id }}">
                          <div class="max-w-sm px-4 py-2 rounded-xl text-sm shadow-sm
                    {{ $msg->sender === 'agent'
                        ? 'bg-blue-500 text-white rounded-br-none'
                        : 'bg-white dark:bg-gray-700 dark:text-white border rounded-bl-none' }}">

                        <p class="whitespace-pre-wrap">{{ $msg->message }}</p>

                    <div class="text-[10px] mt-1 text-black-600 text-right">
                        {{ $msg->created_at->format('H:i') }}
                        @if($msg->sender === 'agent') · موظف @endif
                    </div>
                    </div>
                        </div>
                    @empty
                        <div data-empty class="text-center text-gray-400 text-sm mt-8">
                            لا توجد رسائل بعد
                        </div>
                    @endforelse
                </div>

                {{-- Send Message --}}
                <div class="p-4 border-t dark:border-gray-700 bg-white dark:bg-gray-900 flex gap-2 items-end">
                    <div class="flex-1">
                        {{ $this->form }}
                    </div>
                    <button
                        wire:click="sendReply"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-50 cursor-not-allowed"
                        class="bg-primary-600 hover:bg-primary-700 text-white px-5 py-2 rounded-lg text-sm font-medium transition"
                    >
                        <span wire:loading.remove wire:target="sendReply">إرسال ✉️</span>
                        <span wire:loading wire:target="sendReply">جاري...</span>
                    </button>
                </div>

            @else
                <div class="flex flex-1 items-center justify-center text-gray-400">
                    <div class="text-center">
                        <div class="text-5xl mb-3">💬</div>
                        <p class="text-sm">اختر محادثة من القائمة</p>
                    </div>
                </div>
            @endif

        </div>
    </div>

    <script>
        /* -------------------------------------------------- */
        /*  Scroll to bottom                                   */
        /* -------------------------------------------------- */
        function scrollToBottom() {
            const container = document.getElementById('messages-container');
            if (container) container.scrollTop = container.scrollHeight;
        }

        document.addEventListener('livewire:navigated', scrollToBottom);
        document.addEventListener('livewire:updated',   scrollToBottom);
        scrollToBottom();

        /* -------------------------------------------------- */
        /*  Real-time via Echo + Reverb                        */
        /* -------------------------------------------------- */
        @if($activeConversation)
        (function () {
            const conversationId = {{ $activeConversation->id }};

            function appendMessage(data) {
                const container = document.getElementById('messages-container');
                if (!container) return;

                // منع التكرار
                if (container.querySelector(`[data-msg-id="${data.id}"]`)) return;

                const empty = container.querySelector('[data-empty]');
                if (empty) empty.remove();

                const isAgent = data.sender === 'agent';
                const wrapper = document.createElement('div');
                wrapper.className = `flex ${isAgent ? 'justify-end' : 'justify-start'}`;
                wrapper.setAttribute('data-msg-id', data.id);
                wrapper.innerHTML = `
                    <div class="max-w-sm px-4 py-2 rounded-xl text-sm shadow-sm
                        ${isAgent
                            ? 'bg-primary-600 text-white rounded-br-none'
                            : 'bg-white border rounded-bl-none'}">
                        <p class="whitespace-pre-wrap">${data.message}</p>
                        <div class="text-[10px] mt-1 opacity-60 text-right">
                            ${data.created_at}${isAgent ? ' · موظف' : ''}
                        </div>
                    </div>`;
                container.appendChild(wrapper);
                scrollToBottom();
            }

            function subscribeToConversation(id) {
                if (typeof window.Echo === 'undefined') {
                    setTimeout(() => subscribeToConversation(id), 500);
                    return;
                }

                // channel() دلوقتي بتعمل unbind_all + unsubscribe للقديم تلقائي
                window.Echo.channel(`support.${id}`)
                    .listen('message.sent', (data) => {
                        console.log('🔥 Message received:', data);
                        if (data.sender === 'customer') {
                            appendMessage(data);
                        }
                    });

                console.log('✅ Subscribed to support.' + id);
            }

            // لما Livewire يعمل update، إلغ الاشتراك القديم واشترك من جديد بدون تكرار
            document.addEventListener('livewire:updated', () => {
                subscribeToConversation(conversationId);
            });

            subscribeToConversation(conversationId);

        })();
        @endif
    </script>

</x-filament-panels::page>
