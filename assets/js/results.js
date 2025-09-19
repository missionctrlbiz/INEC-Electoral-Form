document.addEventListener('DOMContentLoaded', () => {
    // --- DYNAMIC LGA FILTER LOGIC ---
    const stateFilter = document.getElementById('state-filter');
    const lgaFilter = document.getElementById('lga-filter');

    function populateLgaFilter(selectedStateId) {
        // Clear current options (except the first one)
        lgaFilter.innerHTML = '<option value="">All LGAs</option>';
        
        if (!selectedStateId) {
            lgaFilter.disabled = true;
            return;
        }

        // Filter the full lgasData array and create new options
        lgasData
            .filter(lga => lga.state_id == selectedStateId)
            .forEach(lga => {
                const option = document.createElement('option');
                option.value = lga.id;
                option.textContent = lga.name;
                // If this LGA was the one previously selected, mark it as selected
                if (lga.id == currentLgaId) {
                    option.selected = true;
                }
                lgaFilter.appendChild(option);
            });
        
        lgaFilter.disabled = false;
    }

    stateFilter.addEventListener('change', () => {
        // When state changes, reset the current LGA ID since it's no longer relevant
        lgaFilter.value = '';
        populateLgaFilter(stateFilter.value);
    });

    // Populate LGAs on initial page load if a state is already selected
    if (stateFilter.value) {
        populateLgaFilter(stateFilter.value);
    }


    // --- MODAL HANDLING LOGIC (Unchanged from your provided code) ---
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

    // Close view modal
    document.getElementById('close-view-modal-btn').addEventListener('click', () => hideModal(viewModal, viewModalContent));
    viewModal.addEventListener('click', (e) => { if(e.target === viewModal) hideModal(viewModal, viewModalContent) });


    // View button functionality
    document.querySelectorAll('.view-btn').forEach(button => {
        button.addEventListener('click', () => {
            document.getElementById('modal-title').textContent = `${button.dataset.puName}`;
            document.getElementById('modal-location').textContent = button.dataset.location;
            document.getElementById('modal-image').src = button.dataset.imagePath;
            const scoresHtml = '<ul class="space-y-1">' + 
                               (button.dataset.scores.length 
                               ? button.dataset.scores.split(', ').map(s => `<li class="flex justify-between items-center border-b pb-1"><span>${s.split(':')[0]}</span><span class="font-bold text-slate-800">${parseInt(s.split(':')[1]).toLocaleString()}</span></li>`).join('') 
                               : '<li>No scores recorded.</li>') + 
                               '</ul>';
            document.getElementById('modal-scores').innerHTML = scoresHtml;
            showModal(viewModal, viewModalContent);
        });
    });

    // Action buttons (Verify, Flag, Delete) functionality
    const confirmTitle = document.getElementById('confirm-title');
    const confirmText = document.getElementById('confirm-text');
    const confirmOkBtn = document.getElementById('confirm-ok-btn');
    const actionForm = document.getElementById('action-form');
    
    document.getElementById('confirm-cancel-btn').addEventListener('click', () => hideModal(confirmModal, confirmModalContent));
    confirmModal.addEventListener('click', (e) => { if(e.target === confirmModal) hideModal(confirmModal, confirmModalContent) });

    
    document.querySelectorAll('.action-btn').forEach(button => {
        button.addEventListener('click', e => {
            const { action, resultId } = e.currentTarget.dataset;
            const actions = {
                verify: { title: 'Verify Result?', text: 'This will mark the result as officially verified and correct.', ok: 'Yes, Verify', class: 'bg-green-600 hover:bg-green-700' },
                flag: { title: 'Flag Result?', text: 'This will mark the result for a secondary, more detailed review.', ok: 'Yes, Flag', class: 'bg-orange-500 hover:bg-orange-600' },
                delete: { title: 'Delete Result?', text: 'This action is permanent and cannot be undone.', ok: 'Yes, Delete', class: 'bg-red-600 hover:bg-red-700' }
            };
            const config = actions[action];
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
        });
    });
});