        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function () {
    var search = document.getElementById('clientSearch');
    if (search) {
        search.addEventListener('input', function () {
            var q = this.value.toLowerCase();
            document.querySelectorAll('[data-client-card]').forEach(function (card) {
                card.style.display = card.getAttribute('data-search').indexOf(q) !== -1 ? '' : 'none';
            });
        });
    }
    document.querySelectorAll('[data-tab-target]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var name = this.getAttribute('data-tab-target');
            document.querySelectorAll('.pms-tabs button').forEach(function (b) { b.classList.remove('active'); });
            document.querySelectorAll('.pms-tab-panel').forEach(function (p) { p.classList.remove('active'); });
            this.classList.add('active');
            var panel = document.getElementById(name);
            if (panel) {
                panel.classList.add('active');
            }
        });
    });
})();
</script>
</body>
</html>
