import { ref, computed, watch, onMounted, onUnmounted, useTemplateRef } from "vue";
import { onClickOutside } from '@vueuse/core';

export function useHierarchicalSelector(props, emit) {
    const searchQuery = ref("");
    const isOpen = ref(false);
    const selectedItems = ref([]);
    const target = useTemplateRef('target');
    const searchInputContainer = useTemplateRef('searchInputContainer');
    const expandedLevel1 = ref(new Set());
    const expandedLevel2 = ref(new Set());

    const flattenedItems = computed(() => {
        const items = [];
        const flatten = (item) => {
            items.push(item);
            if (item.children && item.children.length > 0) {
                item.children.forEach(child => flatten(child));
            }
        };
        props.items.forEach(item => flatten(item));
        return items;
    });

    const filteredItems = computed(() => {
        if (!searchQuery.value) return props.items;

        const query = searchQuery.value.toLowerCase();

        const filterHierarchy = (items) => {
            return items.map(item => {
                const matchesName = item.name.toLowerCase().includes(query);
                const filteredChildren = item.children ? filterHierarchy(item.children) : [];

                if (matchesName || filteredChildren.length > 0) {
                    if (filteredChildren.length > 0) {
                        expandedLevel1.value.add(item.id);
                        filteredChildren.forEach(child => {
                            if (child.children && child.children.length > 0) {
                                expandedLevel2.value.add(child.id);
                            }
                        });
                    }
                    return {
                        ...item,
                        children: filteredChildren
                    };
                }
                return null;
            }).filter(item => item !== null);
        };

        return filterHierarchy(props.items);
    });

    const toggleLevel1 = (id) => {
        if (expandedLevel1.value.has(id)) {
            expandedLevel1.value.delete(id);
        } else {
            expandedLevel1.value.add(id);
        }
    };

    const toggleLevel2 = (id) => {
        if (expandedLevel2.value.has(id)) {
            expandedLevel2.value.delete(id);
        } else {
            expandedLevel2.value.add(id);
        }
    };

    const isSelected = (item) => selectedItems.value.some(sel => sel.id === item.id);

    const getItemLevelLabel = (item) => {
        const findLevel = (searchItem, currentItems, level = 0) => {
            for (const currentItem of currentItems) {
                if (currentItem.id === searchItem.id) {
                    return level;
                }
                if (currentItem.children && currentItem.children.length > 0) {
                    const childLevel = findLevel(searchItem, currentItem.children, level + 1);
                    if (childLevel !== -1) return childLevel;
                }
            }
            return -1;
        };

        const level = findLevel(item, props.items);
        return level !== -1 ? props.levelLabels[level] : '';
    };

    const selectItem = (item) => {
        if (!isSelected(item)) {
            selectedItems.value = [...selectedItems.value, item];
            emit("update:modelValue", selectedItems.value.map(i => i.id))
        } else {
            removeItem(item);
        }
    };

    const removeItem = (item) => {
        selectedItems.value = selectedItems.value.filter(sel => sel.id !== item.id)
        emit("update:modelValue", selectedItems.value.map(i => i.id))
    }

    const open = () => {
        isOpen.value = true;
    };

    const close = () => {
        isOpen.value = false;
        searchQuery.value = "";
    };

    const closeOnEscape = (e) => {
        if (e.key === 'Escape' && isOpen.value) {
            close();
        }
    };

    watch(
        () => props.modelValue,
        (newVal) => {
            if (selectedItems.value.length === 0 && newVal.length > 0) {
                selectedItems.value = flattenedItems.value.filter(item => newVal.includes(item.id));
            }
        },
        { immediate: true, deep: true }
    )

    onMounted(() => {
        if (props.modelValue.length === 0) return;
        const selectedIds = new Set(props.modelValue);
        selectedItems.value = flattenedItems.value.filter(item => selectedIds.has(item.id));
        document.addEventListener('keydown', closeOnEscape);
    });

    onUnmounted(() => {
        document.removeEventListener('keydown', closeOnEscape);
    });

    onClickOutside(target, () => close(), { ignore: [searchInputContainer] });

    return {
        searchQuery,
        isOpen,
        selectedItems,
        target,
        searchInputContainer,
        expandedLevel1,
        expandedLevel2,
        filteredItems,
        toggleLevel1,
        toggleLevel2,
        isSelected,
        getItemLevelLabel,
        selectItem,
        removeItem,
        open,
        close,
        closeOnEscape
    };
}
