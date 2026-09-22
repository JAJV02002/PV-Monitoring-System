<?php
// Profile page - simple user profile settings
?>
<div class="py-3">
    <h2>Perfil de usuario</h2>
    <p class="text-muted">Configura los datos de tu cuenta.</p>

    <form id="profileForm" class="w-50">
        <div class="mb-3">
            <label class="form-label">Nombre</label>
            <input type="text" id="name" name="name" class="form-control" required disabled>
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" id="email" name="email" class="form-control" required disabled>
        </div>
        <div class="mb-3">
            <label class="form-label">Nueva contraseña (opcional)</label>
            <input type="password" id="passwd" name="passwd" class="form-control" disabled>
        </div>
        <input type="hidden" id="uid" name="uid" value="">
        <div class="d-flex gap-2">
            <button type="button" id="editBtn" class="btn btn-outline-primary">Editar</button>
            <button type="submit" id="saveBtn" class="btn btn-primary" style="display:none;">Guardar</button>
            <button type="button" id="cancelBtn" class="btn btn-secondary" style="display:none;">Cancelar</button>
        </div>
    </form>

    <div id="profileMsg" class="mt-3"></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const uid = document.querySelector('meta[name="app-uid"]').content || '';
    const nameInput = document.getElementById('name');
    const emailInput = document.getElementById('email');
    const passwdInput = document.getElementById('passwd');
    const uidInput = document.getElementById('uid');
    const editBtn = document.getElementById('editBtn');
    const saveBtn = document.getElementById('saveBtn');
    const cancelBtn = document.getElementById('cancelBtn');
    const msgEl = document.getElementById('profileMsg');

    let original = {};

    uidInput.value = uid;

    function setReadonly(readonly) {
        nameInput.disabled = readonly;
        emailInput.disabled = readonly;
        passwdInput.disabled = readonly;
        if (readonly) {
            editBtn.style.display = 'inline-block';
            saveBtn.style.display = 'none';
            cancelBtn.style.display = 'none';
        } else {
            editBtn.style.display = 'none';
            saveBtn.style.display = 'inline-block';
            cancelBtn.style.display = 'inline-block';
        }
    }

    // Fetch profile data
    fetch(`/app/api.php?_profile_data&uid=${encodeURIComponent(uid)}`)
        .then(r => r.json())
        .then(data => {
            if (data && data.r !== false) {
                const u = data.user || {};
                nameInput.value = u.name || document.querySelector('meta[name="app-name"]').content || '';
                emailInput.value = u.email || '';
                original = { name: nameInput.value, email: emailInput.value };
                setReadonly(true);
            }
        }).catch(()=>{});

    editBtn.addEventListener('click', () => {
        setReadonly(false);
        passwdInput.value = '';
    });

    cancelBtn.addEventListener('click', () => {
        // restore
        nameInput.value = original.name || '';
        emailInput.value = original.email || '';
        passwdInput.value = '';
        setReadonly(true);
        msgEl.innerHTML = '';
    });

    document.getElementById('profileForm').addEventListener('submit', (ev) => {
        ev.preventDefault();
        const form = ev.target;
        const fd = new FormData();
        fd.append('_update_profile','1');
        fd.append('uid', uidInput.value || '');
        fd.append('name', nameInput.value || '');
        fd.append('email', emailInput.value || '');
        if (passwdInput.value) fd.append('passwd', passwdInput.value);
        fd.append('_csrf', document.querySelector('meta[name="csrf-token"]').getAttribute('content') || '');

        fetch('/app/api.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(j => {
            if (j && j.r) {
                msgEl.innerHTML = '<div class="alert alert-success">Perfil actualizado</div>';
                original = { name: nameInput.value, email: emailInput.value };
                setReadonly(true);
            } else {
                msgEl.innerHTML = '<div class="alert alert-danger">Ocurrió un error</div>';
            }
        }).catch(()=>{
            msgEl.innerHTML = '<div class="alert alert-danger">Ocurrió un error</div>';
        });
    });
});
</script>
