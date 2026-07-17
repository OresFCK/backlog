import { ref } from 'vue';

export const mobileSidebarOpen = ref(false);

export const toggleMobileSidebar = () => {
    mobileSidebarOpen.value = !mobileSidebarOpen.value;
};

export const closeMobileSidebar = () => {
    mobileSidebarOpen.value = false;
};
