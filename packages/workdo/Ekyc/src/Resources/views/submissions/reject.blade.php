<form action="{{ route('ekyc.admin.submissions.reject', $submission->id) }}" method="POST">
    @csrf
    <div class="modal-body">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="reason" class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                    <select name="reason" id="reason" class="form-control" required>
                        <option value="">Select Reason</option>
                        <option value="Blurry Image">Blurry Image</option>
                        <option value="Documents Mismatch">Documents Mismatch</option>
                        <option value="Signature Mismatch">Signature Mismatch</option>
                        <option value="Face Match Failed">Face Match Failed</option>
                        <option value="Incomplete Data">Incomplete Data</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
            </div>
            <div class="col-md-12 mt-3">
                <div class="form-group">
                    <label for="notes" class="form-label">Internal Notes</label>
                    <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Enter detailed feedback here..."></textarea>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-danger">Reject Application</button>
    </div>
</form>
