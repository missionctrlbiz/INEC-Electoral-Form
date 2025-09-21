document.addEventListener('DOMContentLoaded', () => {
    // --- CASCADING LOCATION FILTERS ---
    const stateFilter = document.getElementById('state-filter');
    const lgaFilter = document.getElementById('lga-filter');
    const wardFilter = document.getElementById('ward-filter');

    function populateLgaFilter(selectedStateId) {
        lgaFilter.innerHTML = '<option value="">All LGAs</option>';
        wardFilter.innerHTML = '<option value="">All Wards</option>'; // Also clear wards
        wardFilter.disabled = true;

        if (!selectedStateId) { lgaFilter.disabled = true; return; }

        lgasData
            .filter(lga => lga.state_id == selectedStateId)
            .forEach(lga => {
                const option = document.createElement('option');
                option.value = lga.id;
                option.textContent = lga.name;
                if (lga.id == currentLgaId) { option.selected = true; }
                lgaFilter.appendChild(option);
            });
        lgaFilter.disabled = false;
    }

    function populateWardFilter(selectedLgaId) {
        wardFilter.innerHTML = '<option value="">All Wards</option>';
        if (!selectedLgaId) { wardFilter.disabled = true; return; }

        wardsData
            .filter(ward => ward.lga_id == selectedLgaId)
            .forEach(ward => {
                const option = document.createElement('option');
                option.value = ward.id;
                option.textContent = ward.name;
                if (ward.id == currentWardId) { option.selected = true; }
                wardFilter.appendChild(option);
            });
        wardFilter.disabled = false;
    }

    stateFilter.addEventListener('change', () => {
        lgaFilter.value = '';
        wardFilter.value = '';
        populateLgaFilter(stateFilter.value);
        populateWardFilter(null); // Disable ward filter
    });
    
    lgaFilter.addEventListener('change', () => {
        wardFilter.value = '';
        populateWardFilter(lgaFilter.value);
    });

    // Populate on initial page load
    if (stateFilter.value) { populateLgaFilter(stateFilter.value); }
    if (lgaFilter.value) { populateWardFilter(lgaFilter.value); }


    // --- MODAL HANDLING LOGIC ---
    const viewModal = document.getElementById('view-modal');
    const viewModalContent = document.getElementById('view-modal-content');
    const confirmModal = document.getElementById('confirm-modal');
    const confirmModalContent = document.getElementById('confirm-modal-content');

    const showModal = (modal, content) => {
        modal.classList.remove('hidden', 'pointer-events-none', 'opacity-0');
        content.classList.remove('opacity-0', 'scale-95');
    };
    const hideModal = (modal, content) => {
        modal.classList.add('opacity-0');
        content.classList.add('opacity-0', 'scale-95');
        setTimeout(() => modal.classList.add('hidden', 'pointer-events-none'), 300);
    };

    document.getElementById('close-view-modal-btn').addEventListener('click', () => hideModal(viewModal, viewModalContent));
    viewModal.addEventListener('click', (e) => { if(e.target === viewModal) hideModal(viewModal, viewModalContent) });

    document.querySelectorAll('.view-btn').forEach(button => {
        button.addEventListener('click', () => {
            document.getElementById('modal-title').textContent = `${button.dataset.puName}`;
            document.getElementById('modal-location').textContent = button.dataset.location;
            document.getElementById('modal-image').src = button.dataset.imagePath;
            const scoresHtml = '<ul class="space-y-1">' + 
                               (button.dataset.scores.length 
                               ? button.dataset.scores.split(', ').map(s => `<li class="flex justify-between items-center border-b pb-1"><span>${s.split(':')[0]}</span><span class="font-bold text-slate-800">${parseInt(s.split(':')[1] || 0).toLocaleString()}</span></li>`).join('') 
                               : '<li>No scores recorded.</li>') + 
                               '</ul>';
            document.getElementById('modal-scores').innerHTML = scoresHtml;
            showModal(viewModal, viewModalContent);
        });
    });

    const confirmTitle = document.getElementById('confirm-title');
    const confirmText = document.getElementById('confirm-text');
    const confirmOkBtn = document.getElementById('confirm-ok-btn');
    const actionForm = document.getElementById('action-form');
    
    document.getElementById('confirm-cancel-btn').addEventListener('click', () => hideModal(confirmModal, confirmModalContent));
    confirmModal.addEventListener('click', (e) => { if(e.target === confirmModal) hideModal(confirmModal, confirmModalContent) });
    
    document.querySelectorAll('.action-btn').forEach(button => {
        button.addEventListener('click', e => {
            const { action, resultId } = e.currentTarget.dataset;
            
            // --- FIX: SIMPLIFIED THE ACTIONS OBJECT ---
            // This object now ONLY contains the 'delete' action, which matches the HTML.
            // This prevents the error when trying to find undefined actions like 'verify' or 'flag'.
            const actions = {
                delete: { title: 'Delete Result?', text: 'This action is permanent and cannot be undone.', ok: 'Yes, Delete', class: 'bg-red-600 hover:bg-red-700' }
            };

            const config = actions[action];
            if (config) { // Check if the action exists in our config
                confirmTitle.textContent = config.title;
                confirmText.textContent = config.text;
                confirmOkBtn.textContent = config.ok;
                confirmOkBtn.className = `text-white font-semibold w-full py-2 rounded-lg transition-colors ${config.class}`;
                
                confirmOkBtn.onclick = () => {
                    document.getElementById('action-input').value = action;
                    document.getElementById('result-id-input').value = resultId;
                    actionForm.submit();
                };
                showModal(confirmModal, confirmModalContent);
            }
        });
    });
});