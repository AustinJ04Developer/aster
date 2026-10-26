function loadBatches() {
    const degreeId = document.getElementById('degree').value;
    const batchSelect = document.getElementById('batch');
    const branchSelect = document.getElementById('branch');
    const degreeBranchSelect = document.getElementById('degreebranch');
    
    batchSelect.innerHTML = '<option value="">Select Batch</option>';
    branchSelect.innerHTML = '<option value="">Select Branch</option>';
    degreeBranchSelect.innerHTML = '<option value="">Select Degree Branch</option>';
    
    if (degreeId) {
        $.ajax({
            url: '../ajax/get_batches.php',
            type: 'GET',
            data: { did: degreeId },
            dataType: 'json',
            success: function(data) {
                data.forEach(batch => {
                    const option = document.createElement('option');
                    option.value = batch.BID;
                    option.text = batch.BatchName;
                    batchSelect.appendChild(option);
                });
            },
            error: function(xhr, status, error) {
                console.error('Failed to load batches:', error);
            }
        });
    }
}

function loadBranches() {
    const batchId = document.getElementById('batch').value;
    const branchSelect = document.getElementById('branch');
    const degreeBranchSelect = document.getElementById('degreebranch');
    
    branchSelect.innerHTML = '<option value="">Select Branch</option>';
    degreeBranchSelect.innerHTML = '<option value="">Select Degree Branch</option>';
    
    if (batchId) {
        $.ajax({
            url: '../ajax/get_branches.php',
            type: 'GET',
            data: { bid: batchId },
            dataType: 'json',
            success: function(data) {
                data.forEach(branch => {
                    const option = document.createElement('option');
                    option.value = branch.BRID;
                    option.text = branch.BranchName;
                    branchSelect.appendChild(option);
                });
            },
            error: function(xhr, status, error) {
                console.error('Failed to load branches:', error);
            }
        });
    }
}

function loadDegreeBranches() {
    const branchId = document.getElementById('branch').value;
    const degreeBranchSelect = document.getElementById('degreebranch');
    
    degreeBranchSelect.innerHTML = '<option value="">Select Degree Branch</option>';
    
    if (branchId) {
        $.ajax({
            url: '../ajax/get_degree_branches.php',
            type: 'GET',
            data: { brid: branchId },
            dataType: 'json',
            success: function(data) {
                data.forEach(degreeBranch => {
                    const option = document.createElement('option');
                    option.value = degreeBranch.DBID;
                    option.text = degreeBranch.DegreeBranchName;
                    degreeBranchSelect.appendChild(option);
                });
            },
            error: function(xhr, status, error) {
                console.error('Failed to load degree branches:', error);
            }
        });
    }
}