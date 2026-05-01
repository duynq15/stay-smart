<script setup>
const emit = defineEmits(['open']);
</script>

<template>
    <div class="chat-fab-big" @click="emit('open')" role="button" aria-label="Mở trợ lý AI">
        <div class="chat-bubble-big">
            <div class="chat-avatar-big">S</div>
            <div class="chat-text-big">
                <strong>Smarty</strong>
                <small>Tôi giúp bạn tìm phòng nhé?</small>
            </div>
        </div>
        <span class="ping-dot"></span>
    </div>
</template>

<style scoped>
.chat-fab-big {
    position: fixed;
    bottom: 28px;
    right: 28px;
    z-index: 80;
    cursor: pointer;
    transition: transform 0.3s cubic-bezier(0.2, 0.8, 0.2, 1);
}
.chat-fab-big:hover {
    transform: scale(1.06) translateY(-2px);
}
.chat-fab-big:active {
    transform: scale(0.98);
}

.chat-bubble-big {
    background: var(--cream);
    border-radius: 999px;
    padding: 16px 26px 16px 16px;
    box-shadow: 0 12px 36px -10px rgba(11, 20, 16, 0.32), 0 4px 12px rgba(11, 20, 16, 0.1);
    display: flex;
    align-items: center;
    gap: 16px;
    border: 1.5px solid rgba(31, 155, 106, 0.25);
    animation: floaty-big 4s ease-in-out infinite;
}
@keyframes floaty-big {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-8px); }
}

.chat-avatar-big {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--emerald-500), var(--emerald-900));
    display: grid;
    place-items: center;
    color: var(--cream);
    font-family: var(--serif);
    font-style: italic;
    font-weight: 600;
    font-size: 30px;
    position: relative;
    box-shadow: inset 0 -4px 8px rgba(0, 0, 0, 0.18), 0 4px 12px rgba(31, 155, 106, 0.35);
    flex-shrink: 0;
}
.chat-avatar-big::after {
    content: '';
    position: absolute;
    bottom: 3px;
    right: 3px;
    width: 16px;
    height: 16px;
    background: var(--emerald-300);
    border: 3px solid var(--cream);
    border-radius: 50%;
}

.chat-text-big {
    line-height: 1.3;
    padding-right: 6px;
}
.chat-text-big strong {
    display: block;
    font-family: var(--serif);
    font-weight: 600;
    color: var(--ink-900);
    font-size: 18px;
    letter-spacing: -0.01em;
}
.chat-text-big small {
    color: var(--ink-500);
    font-size: 14px;
}

.ping-dot {
    position: absolute;
    top: 8px;
    right: 8px;
    width: 14px;
    height: 14px;
    background: var(--rust);
    border: 2px solid var(--cream);
    border-radius: 50%;
    animation: ping 2s ease-out infinite;
    box-shadow: 0 0 0 0 rgba(184, 92, 60, 0.6);
}
@keyframes ping {
    0% {
        box-shadow: 0 0 0 0 rgba(184, 92, 60, 0.6);
    }
    70% {
        box-shadow: 0 0 0 14px rgba(184, 92, 60, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(184, 92, 60, 0);
    }
}

@media (max-width: 600px) {
    .chat-fab-big {
        bottom: 18px;
        right: 18px;
    }
    .chat-bubble-big {
        padding: 12px 18px 12px 12px;
        gap: 12px;
    }
    .chat-avatar-big {
        width: 52px;
        height: 52px;
        font-size: 24px;
    }
    .chat-text-big strong { font-size: 15px; }
    .chat-text-big small { font-size: 12px; }
}
</style>
