/**
 * 🎯 RR-SUBMENU - Unified Submenu JavaScript
 * Rei do Rodeio
 */

(function() {
    'use strict';

    /**
     * Initialize a submenu by ID
     * @param {string} menuId - The ID of the submenu container
     * @param {Function} onFilter - Callback when filter changes (receives filter value)
     * @param {Function} onAction - Callback for action cards (receives action value)
     */
    function initSubmenu(menuId, onFilter, onAction) {
        const menu = document.getElementById(menuId);
        if (!menu) return;

        menu.querySelectorAll('.rr-submenu__card').forEach(card => {
            card.addEventListener('click', function() {
                const action = this.dataset.action;
                
                // If it's an action card, call action callback and don't change active state
                if (action && onAction) {
                    onAction(action, this);
                    return;
                }
                
                // Regular filter card - update active state
                menu.querySelectorAll('.rr-submenu__card').forEach(c => {
                    c.classList.remove('rr-submenu__card--active');
                });
                this.classList.add('rr-submenu__card--active');
                
                // Call filter callback
                const filter = this.dataset.filter;
                if (filter && onFilter) {
                    onFilter(filter, this);
                }
            });
        });
    }

    /**
     * Update count for a specific filter in a submenu
     * @param {string} menuId - The ID of the submenu
     * @param {string} filter - The filter value
     * @param {number} count - The new count
     */
    function updateCount(menuId, filter, count) {
        const menu = document.getElementById(menuId);
        if (!menu) return;

        const countEl = menu.querySelector(`[data-count="${filter}"]`);
        if (countEl) {
            countEl.textContent = count;
        }
    }

    /**
     * Update all counts in a submenu
     * @param {string} menuId - The ID of the submenu
     * @param {Object} counts - Object with filter: count pairs
     */
    function updateAllCounts(menuId, counts) {
        Object.entries(counts).forEach(([filter, count]) => {
            updateCount(menuId, filter, count);
        });
    }

    /**
     * Set active card programmatically
     * @param {string} menuId - The ID of the submenu
     * @param {string} filter - The filter value to activate
     */
    function setActive(menuId, filter) {
        const menu = document.getElementById(menuId);
        if (!menu) return;

        menu.querySelectorAll('.rr-submenu__card').forEach(card => {
            const isMatch = card.dataset.filter === filter;
            card.classList.toggle('rr-submenu__card--active', isMatch);
        });
    }

    /**
     * Get currently active filter
     * @param {string} menuId - The ID of the submenu
     * @returns {string|null} The active filter value
     */
    function getActive(menuId) {
        const menu = document.getElementById(menuId);
        if (!menu) return null;

        const active = menu.querySelector('.rr-submenu__card--active');
        return active ? active.dataset.filter : null;
    }

    // Expose globally
    window.RRSubmenu = {
        init: initSubmenu,
        updateCount: updateCount,
        updateAllCounts: updateAllCounts,
        setActive: setActive,
        getActive: getActive
    };

})();
