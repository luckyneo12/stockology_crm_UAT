<form action="{{ route('ekyc.admin.submissions.approve', $submission->id) }}" method="POST">
    @csrf
    <div class="modal-body">
        <div class="row">
            <div class="col-md-12 mb-3">
                <p class="text-muted">Are you sure you want to approve this KYC application? This will mark the identity as verified in the system.</p>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label for="notes" class="form-label">Approval Notes (Optional)</label>
                    <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Enter any notes if needed..."></textarea>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-success">Confirm Approval</button>
    </div>
</form>
