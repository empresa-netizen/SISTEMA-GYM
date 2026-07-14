<div class="modal fade" id="mgSearchModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title">Buscar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('search') }}" method="GET">
                    <div class="input-group">
                        <span class="input-group-text"><i class="ri-search-line"></i></span>
                        <input type="search" name="q" class="form-control" placeholder="Cliente, treino ou fatura..." autofocus>
                        <button class="btn btn-primary" type="submit">Buscar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
