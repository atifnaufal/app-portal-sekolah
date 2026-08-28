<?php $hideNav = true; ?>


<?php $__env->startSection('content'); ?>
<style>
    /* WhatsApp/Telegram Style UI */
    .chat-app { display: flex; flex-direction: column; height: 100vh; background: #f0f2f5; }

    /* Page Header for Inbox */
    .chat-inbox-header {
        position: fixed; top: 0; left: 0; right: 0; z-index: 1000;
        background: #fff; border-bottom: 1px solid #f0f0f0;
        padding: 12px 20px; display: flex; align-items: center; gap: 12px;
    }

    /* Inbox / Group List */
    .inbox-content { flex: 1; overflow-y: auto; padding-top: 70px; padding-bottom: 20px; background: #fff; }
    .group-item {
        display: flex; align-items: center; gap: 15px; padding: 14px 20px;
        border-bottom: 1px solid #f8f9fa; text-decoration: none; color: inherit;
        transition: background 0.2s;
    }
    .group-item:active { background: #f0f2f5; }
    .group-avatar {
        width: 52px; height: 52px; border-radius: 18px;
        background: linear-gradient(135deg, #0088cc, #00aaff);
        display: flex; align-items: center; justify-content: center;
        color: #fff; font-weight: 800; font-size: 20px; flex-shrink: 0;
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    }
    .group-avatar.class { background: linear-gradient(135deg, #10b981, #059669); }
    .group-avatar.eskul { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }

    .inbox-section-label {
        padding: 14px 20px 6px; font-size: 11px; font-weight: 800;
        text-transform: uppercase; letter-spacing: 0.1em; color: #94a3b8;
        display: flex; align-items: center; gap: 8px; background: #fff;
    }
    .inbox-section-label::after { content: ''; flex: 1; height: 1px; background: #f1f5f9; }
    .inbox-section-label i { color: var(--blue); }

    .group-info { flex: 1; min-width: 0; }
    .group-name { font-weight: 800; font-size: 15px; color: #0f172a; margin-bottom: 2px; }
    .group-last-msg { font-size: 13px; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

    /* Chat Thread */
    .chat-header {
        position: fixed; top: 0; left: 0; right: 0; z-index: 1000;
        background: #fff; border-bottom: 1px solid #f0f0f0;
        padding: 10px 15px; display: flex; align-items: center; gap: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.02);
    }
    .chat-header .back-btn {
        width: 36px; height: 36px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        color: #0f172a; font-size: 18px; text-decoration: none;
    }
    .chat-header .back-btn:active { background: #f1f5f9; }

    .chat-messages {
        flex: 1; padding: 75px 15px 90px;
        background-color: #e5ddd5;
        background-image: url("https://user-images.githubusercontent.com/15075759/28719144-86dc0f70-73b1-11e7-911d-60d70fcded21.png");
        overflow-y: auto; display: flex; flex-direction: column;
    }

    .msg-row { display: flex; margin-bottom: 12px; max-width: 85%; }
    .msg-row.mine { align-self: flex-end; flex-direction: row-reverse; }
    .msg-bubble {
        padding: 8px 12px; border-radius: 14px; position: relative;
        box-shadow: 0 1px 2px rgba(0,0,0,0.1); font-size: 14px;
    }
    .msg-row.mine .msg-bubble { background: #dcf8c6; color: #303030; border-top-right-radius: 2px; }
    .msg-row.other .msg-bubble { background: #fff; color: #303030; border-top-left-radius: 2px; }

    .chat-footer {
        position: fixed; bottom: 0; left: 0; right: 0; z-index: 1000;
        background: #f8fafc; padding: 12px 16px; display: flex; align-items: center; gap: 10px;
        border-top: 1px solid #e2e8f0;
    }
    .chat-input {
        flex: 1; background: #fff; border: 1px solid #e2e8f0; border-radius: 20px;
        padding: 10px 16px; font-size: 14px; outline: none;
    }
</style>

<div class="chat-app">
    <?php if(!$activeGroup): ?>
        <!-- Inbox Header -->
        <div class="chat-inbox-header">
            <a href="<?php echo e(route('dashboard')); ?>" class="btn btn-light rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="bi bi-chevron-left h5 mb-0"></i>
            </a>
            <div class="fw-bold" style="font-size: 18px;">Pesan Grup</div>
        </div>

        <div class="inbox-content">
            <div class="px-3 mb-3">
                <div class="input-group input-group-sm bg-light rounded-pill px-2">
                    <span class="input-group-text border-0 bg-transparent text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control border-0 bg-transparent" placeholder="Cari percakapan...">
                </div>
            </div>

            <div class="inbox-section-label"><i class="bi bi-person-lines-fill"></i> Grup Kelas</div>
            <?php $__empty_1 = true; $__currentLoopData = $classGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <a href="<?php echo e(route('chat.index', ['group_id' => $g->id])); ?>" class="group-item">
                    <div class="group-avatar <?php echo e($g->type); ?>">
                        <?php if($g->avatar): ?>
                            <img src="<?php echo e(asset('storage/'.$g->avatar)); ?>" class="rounded-4 w-100 h-100">
                        <?php else: ?>
                            <?php echo e(strtoupper(substr($g->name, 0, 1))); ?>

                        <?php endif; ?>
                    </div>
                    <div class="group-info">
                        <div class="d-flex justify-content-between">
                            <div class="group-name"><?php echo e($g->name); ?></div>
                            <div style="font-size: 10px; color: #94a3b8; font-weight: 600;">
                                <?php echo e($g->lastMessage ? $g->lastMessage->created_at->format('H:i') : ''); ?>

                            </div>
                        </div>
                        <div class="group-last-msg">
                            <?php if($g->lastMessage): ?>
                                <span class="fw-bold text-dark"><?php echo e($g->lastMessage->user->id === $user->id ? 'Anda: ' : explode(' ', $g->lastMessage->user->name)[0] . ': '); ?></span>
                                <?php echo e($g->lastMessage->pesan); ?>

                            <?php else: ?>
                                <span class="fst-italic opacity-50">Belum ada pesan baru</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="text-center py-4 opacity-25">
                    <i class="bi bi-people" style="font-size: 40px;"></i>
                    <div class="fw-bold mt-1 small">Belum ada grup kelas</div>
                </div>
            <?php endif; ?>

            <div class="inbox-section-label"><i class="bi bi-flag-fill"></i> Grup Eskul</div>
            <?php $__empty_1 = true; $__currentLoopData = $eskulGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <a href="<?php echo e(route('chat.index', ['group_id' => $g->id])); ?>" class="group-item">
                    <div class="group-avatar eskul">
                        <?php if($g->avatar): ?>
                            <img src="<?php echo e(asset('storage/'.$g->avatar)); ?>" class="rounded-4 w-100 h-100">
                        <?php else: ?>
                            <?php echo e(strtoupper(substr($g->name, 0, 1))); ?>

                        <?php endif; ?>
                    </div>
                    <div class="group-info">
                        <div class="d-flex justify-content-between">
                            <div class="group-name"><?php echo e($g->name); ?></div>
                            <div style="font-size: 10px; color: #94a3b8; font-weight: 600;">
                                <?php echo e($g->lastMessage ? $g->lastMessage->created_at->format('H:i') : ''); ?>

                            </div>
                        </div>
                        <div class="group-last-msg">
                            <?php if($g->lastMessage): ?>
                                <span class="fw-bold text-dark"><?php echo e($g->lastMessage->user->id === $user->id ? 'Anda: ' : explode(' ', $g->lastMessage->user->name)[0] . ': '); ?></span>
                                <?php echo e($g->lastMessage->pesan); ?>

                            <?php else: ?>
                                <span class="fst-italic opacity-50">Belum ada pesan baru</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="text-center py-4 opacity-25">
                    <i class="bi bi-flag" style="font-size: 40px;"></i>
                    <div class="fw-bold mt-1 small">Belum ada grup eskul</div>
                </div>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <!-- Chat Thread Header -->
        <div class="chat-header">
            <a href="<?php echo e(route('chat.index')); ?>" class="back-btn"><i class="bi bi-chevron-left"></i></a>
            <div class="group-avatar <?php echo e($activeGroup->type); ?> " style="width:38px; height:38px; font-size:14px; border-radius: 12px;">
                <?php if($activeGroup->avatar): ?>
                    <img src="<?php echo e(asset('storage/'.$activeGroup->avatar)); ?>" class="w-100 h-100" style="border-radius:12px;">
                <?php else: ?>
                    <?php echo e(strtoupper(substr($activeGroup->name, 0, 1))); ?>

                <?php endif; ?>
            </div>
            <div style="flex: 1; min-width: 0;">
                <div class="fw-bold text-truncate" style="font-size: 15px;"><?php echo e($activeGroup->name); ?></div>
                <div style="font-size: 10px; color: #10b981; font-weight: 700;"><?php echo e($activeGroup->members->count()); ?> Anggota Online</div>
            </div>
        </div>

        <div class="chat-messages" id="message-list">
            <?php $__empty_1 = true; $__currentLoopData = $messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $msg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php $isMine = $msg->user_id === $user->id; ?>
                <div class="msg-row <?php echo e($isMine ? 'mine' : 'other'); ?>">
                    <div class="msg-bubble">
                        <?php if(!$isMine): ?>
                            <div style="font-size: 10px; font-weight: 800; color: var(--blue); margin-bottom: 2px;"><?php echo e($msg->user->name); ?></div>
                        <?php endif; ?>
                        <?php if($msg->file): ?>
                            <div class="mb-2">
                                <img src="<?php echo e(asset('storage/'.$msg->file)); ?>" class="img-fluid rounded-3" style="max-height: 200px; width: 100%; object-fit: cover;">
                            </div>
                        <?php endif; ?>
                        <div><?php echo e($msg->pesan); ?></div>
                        <div class="text-end" style="font-size: 9px; opacity: 0.5; margin-top: 2px;">
                            <?php echo e($msg->created_at->format('H:i')); ?>

                            <?php if($isMine): ?> <i class="bi bi-check2-all ms-1 text-primary"></i> <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="text-center py-5">
                    <span class="bg-white px-3 py-1 rounded-pill small fw-bold opacity-50">Mulai percakapan cerdas di sini</span>
                </div>
            <?php endif; ?>
        </div>

        <form action="<?php echo e(route('chat.store')); ?>" method="POST" class="chat-footer" id="chatForm" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="chat_group_id" value="<?php echo e($activeGroup->id); ?>">
            <input type="file" name="file" id="fileInput" class="d-none" accept="image/*">
            <button type="button" class="btn btn-link text-secondary p-0" onclick="document.getElementById('fileInput').click()"><i class="bi bi-plus-circle h5 mb-0"></i></button>
            <input name="pesan" id="chatInput" autocomplete="off" class="chat-input" placeholder="Tulis pesan..." >
            <button type="submit" class="btn btn-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="bi bi-send-fill"></i>
            </button>
        </form>
    <?php endif; ?>
</div>

<script>
    <?php if($activeGroup): ?>
    const msgList = document.getElementById('message-list');
    msgList.scrollTop = msgList.scrollHeight;

    const chatForm = document.getElementById('chatForm');
    const chatInput = document.getElementById('chatInput');
    const currentUserId = <?php echo json_encode((int) $user->id, 15, 512) ?>;
    const activeGroupId = <?php echo json_encode((int) $activeGroup->id, 15, 512) ?>;
    let lastMsgId = <?php echo json_encode($messages->last()?->id ?? 0, 15, 512) ?>;

    function appendMessage(data, mine) {
        // Prevent duplicate if already exists in DOM
        if (data.id && document.querySelector(`[data-msg-id="${data.id}"]`)) return;

        const row = document.createElement('div');
        row.className = 'msg-row ' + (mine ? 'mine' : 'other');
        if (data.id) row.setAttribute('data-msg-id', data.id);

        let html = '<div class="msg-bubble">';
        if (!mine) html += '<div style="font-size:10px; font-weight:800; color:var(--blue); margin-bottom:2px;">'+data.nama+'</div>';

        if (data.file_url) {
            html += '<div class="mb-2"><img src="'+data.file_url+'" class="img-fluid rounded-3" style="max-height: 200px; width: 100%; object-fit: cover;"></div>';
        }

        if (data.pesan) {
            html += '<div>'+data.pesan+'</div>';
        }

        html += '<div class="text-end" style="font-size:9px; opacity:0.5; margin-top:2px;">'+data.waktu;
        if (mine) html += ' <i class="bi bi-check2-all ms-1 text-primary"></i>';
        html += '</div></div>';
        row.innerHTML = html;
        msgList.appendChild(row);
        msgList.scrollTop = msgList.scrollHeight;
    }

    const fileInput = document.getElementById('fileInput');

    chatForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const pesan = chatInput.value.trim();
        const hasFile = fileInput.files.length > 0;

        if(!pesan && !hasFile) return;

        // Visual feedback immediately for sender
        let tempFileUrl = null;
        if (hasFile) {
            tempFileUrl = URL.createObjectURL(fileInput.files[0]);
        }

        appendMessage({ pesan, nama: '', waktu: new Date().toLocaleTimeString('id-ID', { hour:'2-digit', minute:'2-digit'}), file_url: tempFileUrl }, true);

        const formData = new FormData(chatForm);
        chatInput.value = '';
        fileInput.value = ''; // Reset file input

        fetch(chatForm.action, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        });
    });

    if (window.Echo) {
        window.Echo.private('portal-chat-group.' + activeGroupId)
            .listen('.new-message', (e) => {
                if (e.user_id !== currentUserId) {
                    appendMessage({ id: e.id, nama: e.nama, pesan: e.pesan, waktu: e.waktu, file_url: e.file_url }, false);
                    if(e.id > lastMsgId) lastMsgId = e.id;
                }
            });
    } else {
        // Fallback Polling if Echo/Broadcasting is not available
        setInterval(() => {
            fetch(`<?php echo e(route('chat.poll')); ?>?group_id=${activeGroupId}&last_id=${lastMsgId}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                if (data.length > 0) {
                    data.forEach(msg => {
                        if (msg.user_id !== currentUserId) {
                            appendMessage(msg, false);
                        }
                        if (msg.id > lastMsgId) lastMsgId = msg.id;
                    });
                }
            });
        }, 3000);
    }
    <?php endif; ?>
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.mobile-app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\app-portal-sekolah\resources\views\mobile\chat.blade.php ENDPATH**/ ?>