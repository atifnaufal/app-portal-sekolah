<?php $hideNav = true; ?>


<?php $__env->startSection('content'); ?>
<style>
    .chat-thread { display: flex; flex-direction: column; height: 100vh; }
    .chat-header {
        position: fixed; top: 0; left: 0; right: 0; z-index: 1000;
        background: #fff; border-bottom: 1px solid #f0f0f0;
        padding: 10px 14px; display: flex; align-items: center; gap: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,.03);
    }
    .chat-header .back-btn {
        width: 36px; height: 36px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        color: #0f172a; font-size: 18px; text-decoration: none;
    }
    .chat-header .back-btn:active { background: #f1f5f9; }
    .hdr-avatar {
        width: 38px; height: 38px; border-radius: 12px; color: #fff; font-weight: 800;
        display: flex; align-items: center; justify-content: center; font-size: 14px; overflow: hidden; flex-shrink: 0;
    }
    .hdr-avatar.school { background: linear-gradient(135deg, #0088cc, #00aaff); }
    .hdr-avatar.class { background: linear-gradient(135deg, #10b981, #059669); }
    .hdr-avatar.eskul { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
    .hdr-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .chat-messages {
        flex: 1; padding: 72px 14px 92px;
        background-color: #e5ddd5;
        background-image: url("https://user-images.githubusercontent.com/15075759/28719144-86dc0f70-73b1-11e7-911d-60d70fcded21.png");
        overflow-y: auto; display: flex; flex-direction: column;
    }
    .msg-row { display: flex; margin-bottom: 12px; max-width: 85%; }
    .msg-row.mine { align-self: flex-end; flex-direction: row-reverse; }
    .msg-bubble {
        padding: 8px 12px; border-radius: 14px; position: relative;
        box-shadow: 0 1px 2px rgba(0,0,0,.1); font-size: 14px;
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

<div class="chat-thread">
    <div class="chat-header">
        <a href="<?php echo e(route('chat.index')); ?>" class="back-btn"><i class="bi bi-chevron-left"></i></a>
        <div class="hdr-avatar <?php echo e($group->type); ?>">
            <?php if($group->avatar): ?>
                <img src="<?php echo e(asset('storage/'.$group->avatar)); ?>">
            <?php else: ?>
                <?php echo e(strtoupper(substr($group->name, 0, 1))); ?>

            <?php endif; ?>
        </div>
        <div style="flex: 1; min-width: 0;">
            <div class="fw-bold text-truncate" style="font-size: 15px;"><?php echo e($group->name); ?></div>
            <div style="font-size: 10px; color: #10b981; font-weight: 700;"><?php echo e($group->members->count()); ?> Anggota</div>
        </div>
        <div class="badge bg-light text-muted rounded-pill" style="font-size: 9px; text-transform: uppercase;">
            <?php echo e($group->type === 'eskul' ? 'Eskul' : 'Kelas'); ?>

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
                    <div class="text-end" style="font-size: 9px; opacity: .5; margin-top: 2px;">
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
        <input type="hidden" name="chat_group_id" value="<?php echo e($group->id); ?>">
        <input type="file" name="file" id="fileInput" class="d-none" accept="image/*">
        <button type="button" class="btn btn-link text-secondary p-0" onclick="document.getElementById('fileInput').click()"><i class="bi bi-plus-circle h5 mb-0"></i></button>
        <input name="pesan" id="chatInput" autocomplete="off" class="chat-input" placeholder="Tulis pesan...">
        <button type="submit" class="btn btn-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
            <i class="bi bi-send-fill"></i>
        </button>
    </form>
</div>

<script>
    const msgList = document.getElementById('message-list');
    msgList.scrollTop = msgList.scrollHeight;

    const chatForm = document.getElementById('chatForm');
    const chatInput = document.getElementById('chatInput');
    const currentUserId = <?php echo json_encode((int) $user->id, 15, 512) ?>;
    const activeGroupId = <?php echo json_encode((int) $group->id, 15, 512) ?>;
    let lastMsgId = <?php echo json_encode($messages->last()?->id ?? 0, 15, 512) ?>;

    function appendMessage(data, mine) {
        if (data.id && document.querySelector(`[data-msg-id="${data.id}"]`)) return;

        const row = document.createElement('div');
        row.className = 'msg-row ' + (mine ? 'mine' : 'other');
        if (data.id) row.setAttribute('data-msg-id', data.id);

        let html = '<div class="msg-bubble">';
        if (!mine) html += '<div style="font-size:10px; font-weight:800; color:var(--blue); margin-bottom:2px;">'+data.nama+'</div>';
        if (data.file_url) {
            html += '<div class="mb-2"><img src="'+data.file_url+'" class="img-fluid rounded-3" style="max-height: 200px; width: 100%; object-fit: cover;"></div>';
        }
        if (data.pesan) html += '<div>'+data.pesan+'</div>';
        html += '<div class="text-end" style="font-size:9px; opacity:.5; margin-top:2px;">'+data.waktu;
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
        if (!pesan && !hasFile) return;

        let tempFileUrl = null;
        if (hasFile) tempFileUrl = URL.createObjectURL(fileInput.files[0]);

        appendMessage({ pesan, nama: '', waktu: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }), file_url: tempFileUrl }, true);

        const formData = new FormData(chatForm);
        chatInput.value = '';
        fileInput.value = '';

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
                    if (e.id > lastMsgId) lastMsgId = e.id;
                }
            });
    } else {
        setInterval(() => {
            fetch(`<?php echo e(route('chat.poll')); ?>?group_id=${activeGroupId}&last_id=${lastMsgId}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                if (data.length > 0) {
                    data.forEach(msg => {
                        if (msg.user_id !== currentUserId) appendMessage(msg, false);
                        if (msg.id > lastMsgId) lastMsgId = msg.id;
                    });
                }
            });
        }, 3000);
    }
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.mobile-app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\app-portal-sekolah\resources\views\mobile\chat-thread.blade.php ENDPATH**/ ?>