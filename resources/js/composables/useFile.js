import { computed, ref } from 'vue';

export const useFileModal = () => {
    const showModal = ref(false);
    const selectedFile = ref(null);

    const openModal = (file) => {
        selectedFile.value = file || null;
        showModal.value = true;
    };

    const closeModal = () => {
        showModal.value = false;
        selectedFile.value = null;
    };

    return { showModal, selectedFile, openModal, closeModal };
};

export const useFile = (form) => {
    const getFilesArray = () => {
        if (!form) return [];

        if (Array.isArray(form.files)) return form.files;
        if (Array.isArray(form.documents)) return form.documents;
        if (Array.isArray(form.attachments)) return form.attachments;

        return [];
    };

    const totalFiles = computed(() => getFilesArray().length);
    const fileCountText = computed(() => `${totalFiles.value} archivo${totalFiles.value === 1 ? '' : 's'}`);

    const handleFileInput = (event) => {
        const files = Array.from(event?.target?.files || []);
        if (!files.length || !form) return;

        if (Array.isArray(form.files)) {
            form.files = [...form.files, ...files];
            return;
        }

        if (Array.isArray(form.documents)) {
            form.documents = [...form.documents, ...files];
            return;
        }

        if (Array.isArray(form.attachments)) {
            form.attachments = [...form.attachments, ...files];
            return;
        }

        form.files = files;
    };

    const removeFile = (index) => {
        if (!form) return;

        if (Array.isArray(form.files)) {
            form.files = form.files.filter((_, i) => i !== index);
            return;
        }

        if (Array.isArray(form.documents)) {
            form.documents = form.documents.filter((_, i) => i !== index);
            return;
        }

        if (Array.isArray(form.attachments)) {
            form.attachments = form.attachments.filter((_, i) => i !== index);
        }
    };

    return {
        fileCountText,
        totalFiles,
        handleFileInput,
        removeFile,
    };
};
