<div id="ussc-chatbot" class="fixed bottom-5 right-5 z-[60]">
    <div id="chatbot-window" class="mb-3 hidden w-[350px] max-w-[calc(100vw-2rem)] overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl">
        <div class="flex items-center justify-between bg-red-900 px-4 py-3 text-white">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-yellow-400 text-red-900"><i class="fa-solid fa-robot"></i></div>
                <div>
                    <h3 class="text-sm font-bold">USSC Assistant</h3>
                    <div class="flex items-center gap-1 text-[10px] text-red-100"><span class="h-2 w-2 rounded-full bg-green-400"></span>Online</div>
                </div>
            </div>
            <button type="button" onclick="toggleChatbot()" class="flex h-8 w-8 items-center justify-center rounded-full hover:bg-red-800" aria-label="Close chatbot">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div id="chatbot-messages" class="h-[360px] space-y-3 overflow-y-auto bg-gray-50 p-4">
            <div class="flex items-start gap-2">
                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-red-900 text-xs text-white"><i class="fa-solid fa-robot"></i></div>
                <div class="max-w-[82%] rounded-2xl rounded-tl-sm border bg-white px-3 py-2 shadow-sm">
                    <p class="whitespace-pre-line text-xs leading-relaxed text-gray-700">Hello! I'm the USSC Assistant. 👋

How can I help you today?</p>
                </div>
            </div>
            <div class="ml-9 flex flex-wrap gap-1.5">
                <button type="button" onclick="sendSuggestion('How do I request a document?')" class="rounded-full border border-red-200 bg-white px-2.5 py-1 text-[11px] text-red-900 hover:bg-red-50">Request Document</button>
                <button type="button" onclick="sendSuggestion('How do I track my request?')" class="rounded-full border border-red-200 bg-white px-2.5 py-1 text-[11px] text-red-900 hover:bg-red-50">Track Request</button>
                <button type="button" onclick="sendSuggestion('Lost and Found')" class="rounded-full border border-red-200 bg-white px-2.5 py-1 text-[11px] text-red-900 hover:bg-red-50">Lost & Found</button>
                <button type="button" onclick="sendSuggestion('Events')" class="rounded-full border border-red-200 bg-white px-2.5 py-1 text-[11px] text-red-900 hover:bg-red-50">Events</button>
            </div>
        </div>

        <form id="chatbot-form" class="flex items-center gap-2 border-t bg-white p-3">
            <input id="chatbot-input" type="text" maxlength="500" autocomplete="off" placeholder="Ask the USSC Assistant..." class="min-w-0 flex-1 rounded-full border border-gray-300 px-4 py-2 text-xs outline-none focus:border-red-900 focus:ring-1 focus:ring-red-900">
            <button id="chatbot-send" type="submit" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-red-900 text-white transition hover:bg-red-800 disabled:cursor-not-allowed disabled:bg-gray-400" aria-label="Send message">
                <i class="fa-solid fa-paper-plane text-xs"></i>
            </button>
        </form>
        <div class="border-t bg-gray-50 px-3 py-1.5 text-center"><p class="text-[9px] text-gray-400">Automated USSC assistance</p></div>
    </div>

    <button id="chatbot-toggle" type="button" onclick="toggleChatbot()" class="ml-auto flex h-14 w-14 items-center justify-center rounded-full bg-red-900 text-xl text-white shadow-xl transition hover:scale-105 hover:bg-red-800" aria-label="Open USSC chatbot">
        <i id="chatbot-toggle-icon" class="fa-solid fa-comments"></i>
    </button>
</div>

<script>
    const chatbotWindow = document.getElementById('chatbot-window');
    const chatbotMessages = document.getElementById('chatbot-messages');
    const chatbotForm = document.getElementById('chatbot-form');
    const chatbotInput = document.getElementById('chatbot-input');
    const chatbotSend = document.getElementById('chatbot-send');
    const chatbotToggleIcon = document.getElementById('chatbot-toggle-icon');

    function toggleChatbot() {
        chatbotWindow.classList.toggle('hidden');
        const isOpen = !chatbotWindow.classList.contains('hidden');
        chatbotToggleIcon.className = isOpen ? 'fa-solid fa-chevron-down' : 'fa-solid fa-comments';
        if (isOpen) setTimeout(() => chatbotInput.focus(), 100);
    }

    function scrollChatToBottom() {
        chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
    }

    function appendUserMessage(message) {
        const wrapper = document.createElement('div');
        wrapper.className = 'flex justify-end';
        const bubble = document.createElement('div');
        bubble.className = 'max-w-[82%] rounded-2xl rounded-tr-sm bg-red-900 px-3 py-2 text-xs leading-relaxed text-white';
        bubble.textContent = message;
        wrapper.appendChild(bubble);
        chatbotMessages.appendChild(wrapper);
        scrollChatToBottom();
    }

    function appendBotMessage(message, suggestions = []) {
        const wrapper = document.createElement('div');
        wrapper.className = 'flex items-start gap-2';

        const avatar = document.createElement('div');
        avatar.className = 'flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-red-900 text-xs text-white';
        avatar.innerHTML = '<i class="fa-solid fa-robot"></i>';

        const content = document.createElement('div');
        content.className = 'max-w-[82%]';

        const bubble = document.createElement('div');
        bubble.className = 'rounded-2xl rounded-tl-sm border bg-white px-3 py-2 shadow-sm';
        const paragraph = document.createElement('p');
        paragraph.className = 'whitespace-pre-line text-xs leading-relaxed text-gray-700';
        paragraph.textContent = message;
        bubble.appendChild(paragraph);
        content.appendChild(bubble);

        if (suggestions.length) {
            const choices = document.createElement('div');
            choices.className = 'mt-2 flex flex-wrap gap-1.5';
            suggestions.forEach((suggestion) => {
                const button = document.createElement('button');
                button.type = 'button';
                button.textContent = suggestion;
                button.className = 'rounded-full border border-red-200 bg-white px-2.5 py-1 text-[10px] text-red-900 hover:bg-red-50';
                button.addEventListener('click', () => sendSuggestion(suggestion));
                choices.appendChild(button);
            });
            content.appendChild(choices);
        }

        wrapper.appendChild(avatar);
        wrapper.appendChild(content);
        chatbotMessages.appendChild(wrapper);
        scrollChatToBottom();
    }

    function appendTypingIndicator() {
        const wrapper = document.createElement('div');
        wrapper.id = 'chatbot-typing';
        wrapper.className = 'flex items-start gap-2';
        wrapper.innerHTML = `
            <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-red-900 text-xs text-white"><i class="fa-solid fa-robot"></i></div>
            <div class="rounded-2xl rounded-tl-sm border bg-white px-3 py-2 shadow-sm">
                <div class="flex gap-1">
                    <span class="h-1.5 w-1.5 animate-bounce rounded-full bg-gray-400"></span>
                    <span class="h-1.5 w-1.5 animate-bounce rounded-full bg-gray-400" style="animation-delay:150ms"></span>
                    <span class="h-1.5 w-1.5 animate-bounce rounded-full bg-gray-400" style="animation-delay:300ms"></span>
                </div>
            </div>`;
        chatbotMessages.appendChild(wrapper);
        scrollChatToBottom();
    }

    async function sendChatMessage(message) {
        const cleanMessage = message.trim();
        if (!cleanMessage) return;

        appendUserMessage(cleanMessage);
        chatbotInput.value = '';
        chatbotInput.disabled = true;
        chatbotSend.disabled = true;
        appendTypingIndicator();

        try {
            const response = await fetch('{{ route('chatbot.reply') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ message: cleanMessage })
            });
            const data = await response.json();
            document.getElementById('chatbot-typing')?.remove();
            if (!response.ok) throw new Error(data.message || 'Unable to process your message.');
            appendBotMessage(data.reply, data.suggestions || []);
        } catch (error) {
            document.getElementById('chatbot-typing')?.remove();
            appendBotMessage('Sorry, I cannot respond right now. Please try again.');
            console.error('Chatbot error:', error);
        } finally {
            chatbotInput.disabled = false;
            chatbotSend.disabled = false;
            chatbotInput.focus();
        }
    }

    function sendSuggestion(message) {
        if (chatbotWindow.classList.contains('hidden')) toggleChatbot();
        sendChatMessage(message);
    }

    chatbotForm.addEventListener('submit', function (event) {
        event.preventDefault();
        sendChatMessage(chatbotInput.value);
    });
</script>
