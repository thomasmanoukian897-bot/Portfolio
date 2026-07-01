import './bootstrap';

const templates = JSON.parse(document.getElementById('exporter-templates-data')?.textContent ?? '[]');

const modal = document.getElementById('exporter-modal');
const templateSelect = document.getElementById('exporter-template-select');
const filenameEl = document.getElementById('exporter-filename');
const descriptionEl = document.getElementById('exporter-description');
const codeEl = document.getElementById('exporter-code');
const copyLabel = document.getElementById('exporter-copy-label');
const panelCode = document.getElementById('exporter-panel-code');
const panelGuide = document.getElementById('exporter-panel-guide');
const fileSelect = document.getElementById('exporter-file-select');

let selectedId = templates[0]?.id ?? 'layout';
let activeTab = 'code';

function getTemplate(id) {
    return templates.find((template) => template.id === id) ?? templates[0];
}

function setSelectedTemplate(id) {
    selectedId = id;
    const template = getTemplate(id);

    if (! template) {
        return;
    }

    if (templateSelect) {
        templateSelect.value = id;
    }

    document.querySelectorAll('[data-exporter-template]').forEach((button) => {
        const isActive = button.getAttribute('data-exporter-template') === id;

        button.classList.toggle('bg-blue-50', isActive);
        button.classList.toggle('border-blue-200', isActive);
        button.classList.toggle('text-blue-700', isActive);
        button.classList.toggle('bg-white', ! isActive);
        button.classList.toggle('border-slate-200', ! isActive);
        button.classList.toggle('hover:bg-slate-100', ! isActive);
        button.classList.toggle('text-slate-600', ! isActive);
    });

    if (filenameEl) {
        filenameEl.textContent = template.filename;
    }

    if (descriptionEl) {
        descriptionEl.textContent = template.description;
    }

    if (codeEl) {
        codeEl.textContent = template.code;
    }
}

function setActiveTab(tab) {
    activeTab = tab;

    document.querySelectorAll('[data-exporter-tab]').forEach((button) => {
        const isActive = button.getAttribute('data-exporter-tab') === tab;

        button.classList.toggle('bg-slate-900', isActive);
        button.classList.toggle('text-white', isActive);
        button.classList.toggle('shadow-xs', isActive);
        button.classList.toggle('text-slate-600', ! isActive);
        button.classList.toggle('hover:text-slate-950', ! isActive);
    });

    panelCode?.classList.toggle('hidden', tab !== 'code');
    panelGuide?.classList.toggle('hidden', tab !== 'guide');
    fileSelect?.classList.toggle('hidden', tab !== 'code');
}

function openModal() {
    if (! modal) {
        return;
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    if (! modal) {
        return;
    }

    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.style.overflow = '';
}

function toggleModal() {
    if (modal?.classList.contains('hidden')) {
        openModal();
    } else {
        closeModal();
    }
}

async function copyCode() {
    const template = getTemplate(selectedId);

    if (! template?.code) {
        return;
    }

    await navigator.clipboard.writeText(template.code);

    if (copyLabel) {
        copyLabel.textContent = 'Copied!';
        setTimeout(() => {
            copyLabel.textContent = 'Copy Code';
        }, 2000);
    }
}

function downloadCode() {
    const template = getTemplate(selectedId);

    if (! template?.code) {
        return;
    }

    const parts = template.filename.split('/');
    const filename = parts[parts.length - 1];
    const blob = new Blob([template.code], { type: 'text/plain;charset=utf-8' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');

    link.href = url;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
}

document.querySelectorAll('[data-exporter-open]').forEach((button) => {
    button.addEventListener('click', openModal);
});

document.querySelectorAll('[data-exporter-close]').forEach((element) => {
    element.addEventListener('click', closeModal);
});

document.querySelectorAll('[data-exporter-tab]').forEach((button) => {
    button.addEventListener('click', () => {
        setActiveTab(button.getAttribute('data-exporter-tab') ?? 'code');
    });
});

document.querySelectorAll('[data-exporter-template]').forEach((button) => {
    button.addEventListener('click', () => {
        setSelectedTemplate(button.getAttribute('data-exporter-template') ?? selectedId);
    });
});

templateSelect?.addEventListener('change', (event) => {
    setSelectedTemplate(event.target.value);
});

document.getElementById('exporter-copy')?.addEventListener('click', copyCode);
document.getElementById('exporter-download')?.addEventListener('click', downloadCode);

document.addEventListener('keydown', (event) => {
    if ((event.metaKey || event.ctrlKey) && event.key === 'k') {
        event.preventDefault();
        toggleModal();
    }

    if (event.key === 'Escape' && modal && ! modal.classList.contains('hidden')) {
        closeModal();
    }
});

setSelectedTemplate(selectedId);
setActiveTab(activeTab);
