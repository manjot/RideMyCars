{{-- Reusable In-App Trip Chat Modal (Issue 3.7: In-App Chat alongside WhatsApp) --}}
<div id="rmcTripChatModal"
     style="display:none; position:fixed; inset:0; z-index:99999; background:rgba(2, 6, 23, 0.72); backdrop-filter:blur(6px); align-items:center; justify-content:center; padding:16px;">
    <div style="width:100%; max-width:440px; background:#0f172a; border:1px solid rgba(255,255,255,0.14); border-radius:22px; overflow:hidden; box-shadow:0 25px 60px rgba(0,0,0,0.65); display:flex; flex-direction:column; height:540px; max-height:88vh;">
        {{-- Header --}}
        <div style="padding:14px 18px; background:linear-gradient(135deg,#1e1b4b,#0f172a); border-bottom:1px solid rgba(255,255,255,0.1); display:flex; align-items:center; justify-content:space-between;">
            <div style="display:flex; align-items:center; gap:10px;">
                <div style="width:38px; height:38px; border-radius:50%; background:rgba(245,158,11,0.18); border:1px solid rgba(245,158,11,0.4); display:flex; align-items:center; justify-content:center; font-size:18px;">
                    💬
                </div>
                <div>
                    <div id="rmcChatPeerName" style="color:#ffffff; font-weight:800; font-size:14.5px; line-height:1.2;">In-App Trip Chat</div>
                    <div style="display:flex; align-items:center; gap:6px; margin-top:2px;">
                        <span style="width:7px; height:7px; border-radius:50%; background:#10b981; display:inline-block;"></span>
                        <span id="rmcChatSubTitle" style="color:#94a3b8; font-size:11px; font-weight:600;">Live Trip Messaging</span>
                    </div>
                </div>
            </div>
            <button type="button" onclick="window.closeTripChat()"
                    style="width:32px; height:32px; border-radius:50%; background:rgba(255,255,255,0.08); border:none; color:#cbd5e1; font-size:15px; cursor:pointer; display:flex; align-items:center; justify-content:center;">
                ✕
            </button>
        </div>

        {{-- Messages Container --}}
        <div id="rmcChatMessagesList"
             style="flex:1; overflow-y:auto; padding:14px 16px; display:flex; flex-direction:column; gap:10px; background:#0b1120;">
            <div style="text-align:center; color:#64748b; font-size:12px; margin-top:40px;">Loading messages...</div>
        </div>

        {{-- Quick Replies --}}
        <div style="padding:8px 12px; background:#0f172a; border-top:1px solid rgba(255,255,255,0.06); display:flex; gap:6px; overflow-x:auto; white-space:nowrap;">
            <button type="button" onclick="window.sendQuickTripChat('I am on my way!')" style="padding:5px 10px; border-radius:999px; background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.1); color:#e2e8f0; font-size:11px; font-weight:600; cursor:pointer;">🚗 On my way</button>
            <button type="button" onclick="window.sendQuickTripChat('I have arrived at the pickup point.')" style="padding:5px 10px; border-radius:999px; background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.1); color:#e2e8f0; font-size:11px; font-weight:600; cursor:pointer;">📍 I have arrived</button>
            <button type="button" onclick="window.sendQuickTripChat('Coming out in 2 minutes.')" style="padding:5px 10px; border-radius:999px; background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.1); color:#e2e8f0; font-size:11px; font-weight:600; cursor:pointer;">⏱️ 2 mins</button>
            <button type="button" onclick="window.sendQuickTripChat('Thank you!')" style="padding:5px 10px; border-radius:999px; background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.1); color:#e2e8f0; font-size:11px; font-weight:600; cursor:pointer;">🙏 Thank you</button>
        </div>

        {{-- Input Box --}}
        <form onsubmit="window.submitTripChatMessage(event)"
              style="padding:12px 14px; background:#0f172a; border-top:1px solid rgba(255,255,255,0.1); display:flex; align-items:center; gap:8px;">
            <input type="text" id="rmcChatInput"
                   placeholder="Type a message..."
                   autocomplete="off"
                   style="flex:1; padding:10px 14px; border-radius:999px; background:#1e293b; border:1px solid rgba(255,255,255,0.14); color:#ffffff; font-size:13px; outline:none;" />
            <button type="submit" id="rmcChatSendBtn"
                    style="padding:10px 18px; border-radius:999px; background:linear-gradient(135deg,#f59e0b,#d97706); border:none; color:#0f172a; font-weight:800; font-size:12.5px; cursor:pointer;">
                Send
            </button>
        </form>
    </div>
</div>

<script>
(function() {
    if (window.__rmcTripChatInitialized) return;
    window.__rmcTripChatInitialized = true;

    let currentChat = {
        type: 'ride',
        id: null,
        peerName: 'Trip Chat',
        senderRole: '{{ auth()->check() && auth()->user()->role === "driver" ? "driver" : "rider" }}',
        senderName: '{{ auth()->check() ? addslashes(auth()->user()->name) : "Customer" }}',
        timer: null
    };

    function escapeHtml(str) {
        return String(str || '').replace(/[&<>"']/g, function(m) {
            return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m];
        });
    }

    function renderMessages(messages) {
        const box = document.getElementById('rmcChatMessagesList');
        if (!box) return;
        if (!Array.isArray(messages) || messages.length === 0) {
            box.innerHTML = '<div style="text-align:center; color:#64748b; font-size:12.5px; margin-top:48px;">No messages yet. Say hello or use a quick reply below!</div>';
            return;
        }
        box.innerHTML = messages.map(m => {
            const isMine = String(m.sender_role || '').toLowerCase() === String(currentChat.senderRole || '').toLowerCase();
            return `
                <div style="display:flex; flex-direction:column; align-items:${isMine ? 'flex-end' : 'flex-start'};">
                    <div style="font-size:10px; color:#94a3b8; margin-bottom:2px; padding:0 4px;">
                        ${escapeHtml(m.sender_name || m.sender_role)} • ${escapeHtml(m.time || '')}
                    </div>
                    <div style="max-width:80%; padding:9px 13px; border-radius:${isMine ? '16px 16px 4px 16px' : '16px 16px 16px 4px'}; background:${isMine ? 'linear-gradient(135deg,#f59e0b,#d97706)' : '#1e293b'}; color:${isMine ? '#0f172a' : '#f8fafc'}; font-size:13px; font-weight:${isMine ? '700' : '500'}; line-height:1.4; border:1px solid ${isMine ? 'transparent' : 'rgba(255,255,255,0.08)'};">
                        ${escapeHtml(m.message)}
                    </div>
                </div>
            `;
        }).join('');
        box.scrollTop = box.scrollHeight;
    }

    async function fetchChatMessages() {
        if (!currentChat.id) return;
        try {
            const res = await fetch(`/api/chat/${encodeURIComponent(currentChat.type)}/${encodeURIComponent(currentChat.id)}`, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();
            if (data && data.success) {
                renderMessages(data.messages || []);
            }
        } catch (e) {}
    }

    window.openTripChat = function(tripType, tripId, peerName, senderRole, senderName) {
        currentChat.type = tripType || 'ride';
        currentChat.id = tripId;
        if (peerName) currentChat.peerName = peerName;
        if (senderRole) currentChat.senderRole = senderRole;
        if (senderName) currentChat.senderName = senderName;

        const modal = document.getElementById('rmcTripChatModal');
        const titleEl = document.getElementById('rmcChatPeerName');
        const subEl = document.getElementById('rmcChatSubTitle');
        if (titleEl) titleEl.textContent = currentChat.peerName || 'In-App Trip Chat';
        if (subEl) subEl.textContent = `Trip #${currentChat.id} • Live In-App Chat`;
        if (modal) modal.style.display = 'flex';

        fetchChatMessages();
        if (currentChat.timer) clearInterval(currentChat.timer);
        currentChat.timer = setInterval(fetchChatMessages, 4000);
    };

    window.closeTripChat = function() {
        const modal = document.getElementById('rmcTripChatModal');
        if (modal) modal.style.display = 'none';
        if (currentChat.timer) {
            clearInterval(currentChat.timer);
            currentChat.timer = null;
        }
    };

    window.sendQuickTripChat = function(text) {
        const input = document.getElementById('rmcChatInput');
        if (input) {
            input.value = text;
            window.submitTripChatMessage(new Event('submit'));
        }
    };

    window.submitTripChatMessage = async function(e) {
        if (e && e.preventDefault) e.preventDefault();
        const input = document.getElementById('rmcChatInput');
        if (!input || !currentChat.id) return;
        const msg = input.value.trim();
        if (!msg) return;
        input.value = '';

        try {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
            const res = await fetch(`/api/chat/${encodeURIComponent(currentChat.type)}/${encodeURIComponent(currentChat.id)}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf
                },
                body: JSON.stringify({
                    message: msg,
                    sender_role: currentChat.senderRole,
                    sender_name: currentChat.senderName
                })
            });
            if (res.ok) {
                await fetchChatMessages();
            }
        } catch (err) {}
    };
})();
</script>

