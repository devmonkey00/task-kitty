import { create } from 'zustand';

/**
 * Global UI state for Task Kitty enterprise modules.
 * Domain data remains in Inertia props; use this for client-only state.
 */
export const useAppStore = create((set) => ({
    sidebarOpen: true,
    /** @type {'offline'|'connecting'|'connected'|'disconnected'|'unavailable'|'failed'} */
    connectionStatus: 'offline',
    activeModule: 'dashboard',

    setSidebarOpen: (sidebarOpen) => set({ sidebarOpen }),
    toggleSidebar: () =>
        set((state) => ({ sidebarOpen: !state.sidebarOpen })),
    setConnectionStatus: (connectionStatus) =>
        set({ connectionStatus }),
    setActiveModule: (activeModule) => set({ activeModule }),
}));
