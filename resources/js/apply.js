import Cropper from 'cropperjs';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

const JPEG_QUALITY = 0.9;

function initCropper({
    inputId,
    modalId,
    imageId,
    previewId,
    placeholderId,
    hiddenId,
    aspectRatio,
    outputWidth,
    outputHeight,
}) {
    const input = document.getElementById(inputId);
    const modal = document.getElementById(modalId);
    const image = document.getElementById(imageId);
    if (!input || !modal || !image) return;

    const hidden = document.getElementById(hiddenId);
    const preview = document.getElementById(previewId);
    const placeholder = placeholderId ? document.getElementById(placeholderId) : null;
    const cropButton = modal.querySelector(`.crop-${modalId}`);
    const closeButtons = modal.querySelectorAll(`.close-${modalId}`);

    let cropper = null;

    const destroyCropper = () => {
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
    };

    const open = () => {
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    };

    const close = () => {
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        destroyCropper();
        image.removeAttribute('src');
        input.value = '';
    };

    closeButtons.forEach((button) => button.addEventListener('click', () => close()));

    modal.addEventListener('click', (event) => {
        if (event.target === modal) close();
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !modal.classList.contains('hidden')) close();
    });

    cropButton?.addEventListener('click', () => {
        if (!cropper) return;

        const canvas = cropper.getCroppedCanvas({
            width: outputWidth,
            height: outputHeight,
            fillColor: '#ffffff',
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high',
        });
        if (!canvas) return;

        const dataUrl = canvas.toDataURL('image/jpeg', JPEG_QUALITY);

        if (hidden) hidden.value = dataUrl;
        if (preview) {
            preview.src = dataUrl;
            preview.classList.remove('hidden');
        }
        if (placeholder) placeholder.classList.add('hidden');

        close();
    });

    input.addEventListener('change', (event) => {
        const file = event.target.files?.[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = (loadEvent) => {
            destroyCropper();
            image.src = loadEvent.target.result;
            open();

            cropper = new Cropper(image, {
                aspectRatio,
                viewMode: 1,
                autoCropArea: 0.9,
                dragMode: 'move',
                cropBoxMovable: true,
                cropBoxResizable: true,
                toggleDragModeOnDblclick: false,
                responsive: true,
                background: false,
            });
        };
        reader.readAsDataURL(file);
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initCropper({
        inputId: 'photo_input',
        modalId: 'photo_modal',
        imageId: 'photo_cropper_image',
        previewId: 'photo_preview',
        placeholderId: 'photo_placeholder',
        hiddenId: 'photo_base64',
        aspectRatio: 1,
        outputWidth: 300,
        outputHeight: 300,
    });

    initCropper({
        inputId: 'signature_input',
        modalId: 'signature_modal',
        imageId: 'signature_cropper_image',
        previewId: 'signature_preview',
        placeholderId: 'signature_placeholder',
        hiddenId: 'signature_base64',
        aspectRatio: 300 / 80,
        outputWidth: 300,
        outputHeight: 80,
    });
});
