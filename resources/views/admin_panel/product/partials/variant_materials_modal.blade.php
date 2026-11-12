<style>
    .mat-trigger-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        background: #ffffff;
        border: 1.5px solid #cbd5e1;
        border-radius: 8px;
        padding: 5px 11px;
        font-size: 12.5px;
        font-weight: 500;
        color: #1e293b;
        white-space: nowrap;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .mat-trigger-btn:hover,
    .mat-trigger-btn:focus {
        border-color: #334155;
        background: #f8fafc;
        color: #0f172a;
        outline: none;
    }
    .mat-trigger-btn svg {
        width: 14px;
        height: 14px;
        flex-shrink: 0;
        color: #334155;
    }
    .mat-cost-display {
        font-size: 13px;
        font-weight: 500;
        color: #334155;
        white-space: nowrap;
    }
    .mat-profit-display {
        font-size: 13px;
        font-weight: 600;
        white-space: nowrap;
    }
    .mat-profit-display.is-positive {
        color: #15803d;
    }
    .mat-profit-display.is-negative {
        color: #dc2626;
    }
    .mat-profit-display.is-neutral {
        color: #64748b;
    }

    /* Materials Modal Styles matching reference UI */
    #variantMaterialsModal .modal-content {
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        box-shadow: 0 20px 50px rgba(15, 23, 42, 0.16);
        overflow: hidden;
    }
    #variantMaterialsModal .mat-modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 18px 22px 10px;
        background: #ffffff;
    }
    #variantMaterialsModal .mat-modal-title {
        font-size: 17px;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
    }
    #variantMaterialsModal .mat-modal-close {
        width: 34px;
        height: 34px;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        color: #334155;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        line-height: 1;
        cursor: pointer;
        transition: background 0.15s ease;
    }
    #variantMaterialsModal .mat-modal-close:hover {
        background: #f1f5f9;
        color: #0f172a;
    }
    #variantMaterialsModal .mat-modal-body {
        padding: 8px 22px 18px;
        background: #ffffff;
    }
    .mat-col-headers {
        display: grid;
        grid-template-columns: 1fr 140px 38px;
        gap: 10px;
        margin-bottom: 6px;
        font-size: 12.5px;
        color: #64748b;
        font-weight: 500;
    }
    .mat-item-row {
        display: grid;
        grid-template-columns: 1fr 140px 38px;
        gap: 10px;
        align-items: center;
        margin-bottom: 10px;
    }
    .mat-item-row input {
        height: 40px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 6px 12px;
        font-size: 14px;
        color: #0f172a;
        background: #ffffff;
        width: 100%;
    }
    .mat-item-row input:focus {
        border-color: #4f46e5;
        box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.12);
        outline: none;
    }
    .mat-row-del {
        width: 38px;
        height: 40px;
        border-radius: 8px;
        border: 1px solid #fecaca;
        background: #fff5f5;
        color: #dc2626;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .mat-row-del:hover {
        background: #fee2e2;
        border-color: #f87171;
    }
    .mat-add-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        height: 36px;
        padding: 0 14px;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        color: #0f172a;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        margin-top: 2px;
        transition: background 0.15s ease;
    }
    .mat-add-btn:hover {
        background: #f8fafc;
        border-color: #94a3b8;
    }
    .mat-summary-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
        margin-top: 18px;
    }
    .mat-summary-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 12px 14px;
    }
    .mat-summary-box .mat-sum-label {
        font-size: 12.5px;
        color: #64748b;
        margin-bottom: 4px;
        font-weight: 500;
    }
    .mat-summary-box .mat-sum-value {
        font-size: 20px;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.2;
    }
    .mat-summary-box .mat-sum-value.is-positive {
        color: #15803d;
    }
    .mat-summary-box .mat-sum-value.is-negative {
        color: #dc2626;
    }
    #variantMaterialsModal .mat-modal-footer {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
        padding: 14px 22px 18px;
        background: #ffffff;
        border-top: 1px solid #f1f5f9;
    }
    .mat-footer-btn {
        height: 38px;
        padding: 0 16px;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        color: #0f172a;
        font-size: 13.5px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .mat-footer-btn:hover {
        background: #f8fafc;
        border-color: #94a3b8;
    }
    .mat-footer-btn-save {
        border-color: #cbd5e1;
        background: #ffffff;
        color: #0f172a;
    }
    .mat-footer-btn-save:hover {
        background: #0f172a;
        border-color: #0f172a;
        color: #ffffff;
    }
    @media (max-width: 575.98px) {
        .mat-col-headers,
        .mat-item-row {
            grid-template-columns: 1fr 100px 36px;
        }
        .mat-summary-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

{{-- Variant Materials & Services Modal --}}
<div id="variantMaterialsModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 680px;">
        <div class="modal-content">
            <div class="mat-modal-header">
                <h5 class="mat-modal-title">Materials and services &middot; <span id="matModalVariantLabel">Variant</span></h5>
                <button type="button" class="mat-modal-close" id="matModalCloseBtn" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="mat-modal-body">
                <div class="mat-col-headers">
                    <div>Name (material or service)</div>
                    <div>Amount (Rs)</div>
                    <div></div>
                </div>
                <div id="matModalRows"></div>
                <button type="button" class="mat-add-btn" id="matModalAddRowBtn">
                    <i class="fas fa-plus" style="font-size: 11px;"></i> Add material
                </button>

                <div class="mat-summary-grid">
                    <div class="mat-summary-box">
                        <div class="mat-sum-label">Total cost</div>
                        <div class="mat-sum-value" id="matModalTotalCost">Rs 0</div>
                    </div>
                    <div class="mat-summary-box">
                        <div class="mat-sum-label">Sale price</div>
                        <div class="mat-sum-value" id="matModalSalePrice">Rs 0</div>
                    </div>
                    <div class="mat-summary-box">
                        <div class="mat-sum-label">Profit</div>
                        <div class="mat-sum-value is-positive" id="matModalProfit">Rs 0</div>
                    </div>
                </div>
            </div>
            <div class="mat-modal-footer">
                <button type="button" class="mat-footer-btn" id="matModalApplyAllBtn">
                    Apply to all <span id="matModalTotalVariants">1</span> variants
                </button>
                <button type="button" class="mat-footer-btn mat-footer-btn-save" id="matModalSaveBtn">
                    Save
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const FLASK_SVG = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 3h6"/><path d="M10 3v5.2a2 2 0 0 1-.4 1.2L4.7 16.5A2.5 2.5 0 0 0 6.7 20.5h10.6a2.5 2.5 0 0 0 2-4l-4.9-7.1a2 2 0 0 1-.4-1.2V3"/><path d="M6.5 15h11"/></svg>';

    let activeVariantRow = null;

    function parseMaterialsJson(raw) {
        if (Array.isArray(raw)) return normalizeList(raw);
        if (!raw || typeof raw !== 'string') return [];
        try {
            const parsed = JSON.parse(raw);
            return Array.isArray(parsed) ? normalizeList(parsed) : [];
        } catch (e) {
            return [];
        }
    }

    function normalizeList(list) {
        return list
            .filter(item => item && typeof item === 'object')
            .map(item => ({
                name: String(item.name ?? '').trim(),
                amount: isNaN(parseFloat(item.amount)) ? 0 : parseFloat(item.amount)
            }))
            .filter(item => item.name !== '' || item.amount !== 0);
    }

    function formatRs(val) {
        const num = parseFloat(val) || 0;
        const clean = Math.abs(num - Math.round(num)) < 0.005
            ? Math.round(num).toLocaleString()
            : parseFloat(num.toFixed(2)).toLocaleString(undefined, { maximumFractionDigits: 2 });
        return 'Rs ' + clean;
    }

    function escapeAttr(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    function getMetrics(materials, salePrice, costOverride = null) {
        const list = normalizeList(materials);
        const materialsCost = list.reduce((sum, m) => sum + (parseFloat(m.amount) || 0), 0);
        const totalCost = costOverride !== null ? (parseFloat(costOverride) || 0) : materialsCost;
        const sale = parseFloat(salePrice) || 0;
        const profit = sale - totalCost;
        const pct = sale > 0 ? Math.round((profit / sale) * 100) : 0;
        return {
            list,
            count: list.length,
            totalCost,
            sale,
            profit,
            pct
        };
    }

    function buildMaterialsCellHtml(initialMaterials) {
        const list = parseMaterialsJson(initialMaterials);
        const jsonStr = escapeAttr(JSON.stringify(list));

        return `
            <td class="p-1 text-center mat-col-cell">
                <input type="hidden" name="variant_materials[]" class="variant-materials-input" value="${jsonStr}">
                <button type="button" class="mat-trigger-btn variant-materials-btn">
                    ${FLASK_SVG}
                    <span>Materials (<span class="mat-count">${list.length}</span>)</span>
                </button>
            </td>
        `;
    }

    function buildProfitCellHtml(initialMaterials, initialSalePrice, initialPurchPrice = null) {
        const list = parseMaterialsJson(initialMaterials);
        const costOverride = (initialPurchPrice !== null && initialPurchPrice !== '' && !isNaN(parseFloat(initialPurchPrice)))
            ? parseFloat(initialPurchPrice)
            : null;
        const metrics = getMetrics(list, initialSalePrice, costOverride);
        const profitClass = metrics.profit > 0
            ? 'is-positive'
            : (metrics.profit < 0 ? 'is-negative' : 'is-neutral');

        return `
            <td class="p-1 text-nowrap profit-col-cell">
                <span class="mat-profit-display ${profitClass}">${formatRs(metrics.profit)} (${metrics.pct}%)</span>
            </td>
        `;
    }

    function getRowMetrics(tr) {
        const matInp = tr?.querySelector('input[name="variant_materials[]"]');
        const saleInp = tr?.querySelector('input[name="variant_sale_price[]"]');
        const purchInp = tr?.querySelector('input[name="variant_purchase_price[]"]');
        const list = parseMaterialsJson(matInp ? matInp.value : '[]');
        const costOverride = (purchInp && purchInp.value !== '' && !isNaN(parseFloat(purchInp.value)))
            ? parseFloat(purchInp.value)
            : null;
        return getMetrics(list, saleInp ? saleInp.value : 0, costOverride);
    }

    function updateRowUI(tr) {
        if (!tr) return;
        const m = getRowMetrics(tr);
        const countEl = tr.querySelector('.mat-count');
        const profitEl = tr.querySelector('.mat-profit-display');

        if (countEl) countEl.textContent = m.count;
        if (profitEl) {
            profitEl.textContent = `${formatRs(m.profit)} (${m.pct}%)`;
            profitEl.classList.remove('is-positive', 'is-negative', 'is-neutral');
            profitEl.classList.add(m.profit > 0 ? 'is-positive' : (m.profit < 0 ? 'is-negative' : 'is-neutral'));
        }

        syncRowMobileSummary(tr, m);
    }

    function syncRowMobileSummary(tr, metrics) {
        const variantsBody = document.getElementById('variantsBody');
        const mobileBody = document.getElementById('mobileVariantsBody');
        if (!variantsBody || !mobileBody) return;
        const idx = Array.from(variantsBody.children).indexOf(tr);
        if (idx < 0) return;
        const card = mobileBody.querySelector(`.mob-variant-card[data-mob-idx="${idx}"]`);
        if (!card) return;

        const mobCount = card.querySelector('.mob-mat-count');
        const mobProfit = card.querySelector('.mob-profit-display');
        if (mobCount) mobCount.textContent = metrics.count;
        if (mobProfit) {
            mobProfit.textContent = `${formatRs(metrics.profit)} (${metrics.pct}%)`;
            mobProfit.style.color = metrics.profit > 0 ? '#15803d' : (metrics.profit < 0 ? '#dc2626' : '#64748b');
        }
    }

    function updateAllRowsUI() {
        const variantsBody = document.getElementById('variantsBody');
        if (!variantsBody) return;
        Array.from(variantsBody.children).forEach(tr => updateRowUI(tr));
    }

    function buildVariantLabel(tr) {
        const name = (tr.querySelector('input[name="variant_name[]"]')?.value || '').trim();
        const size = (tr.querySelector('input[name="variant_size[]"]')?.value || '').trim();
        const color = (tr.querySelector('input[name="variant_color[]"]')?.value || '').trim();
        const extras = [size, color].filter(v => v && v !== '-');
        if (name && extras.length > 0) return `${name} (${extras.join(', ')})`;
        if (name) return name;
        if (extras.length > 0) return extras.join(', ');
        return 'Variant';
    }

    function renderModalItemRow(nameVal = '', amountVal = '') {
        const container = document.getElementById('matModalRows');
        if (!container) return;

        const row = document.createElement('div');
        row.className = 'mat-item-row';
        row.innerHTML = `
            <input type="text" class="mat-row-name" value="${escapeAttr(nameVal)}" placeholder="e.g. Logo, Fabric, Stitching" autocomplete="off">
            <input type="number" step="any" min="0" class="mat-row-amount" value="${escapeAttr(amountVal)}" placeholder="0">
            <button type="button" class="mat-row-del" title="Remove item"><i class="fas fa-trash"></i></button>
        `;
        container.appendChild(row);
        return row;
    }

    function collectModalMaterials() {
        const rows = document.querySelectorAll('#matModalRows .mat-item-row');
        const list = [];
        rows.forEach(row => {
            const name = (row.querySelector('.mat-row-name')?.value || '').trim();
            const rawAmt = row.querySelector('.mat-row-amount')?.value;
            const amount = rawAmt === '' ? 0 : (parseFloat(rawAmt) || 0);
            if (name !== '' || amount > 0) {
                list.push({ name, amount });
            }
        });
        return list;
    }

    function updateModalSummary() {
        if (!activeVariantRow) return;
        const salePrice = parseFloat(activeVariantRow.querySelector('input[name="variant_sale_price[]"]')?.value || 0) || 0;
        const list = collectModalMaterials();
        const metrics = getMetrics(list, salePrice);

        const costEl = document.getElementById('matModalTotalCost');
        const saleEl = document.getElementById('matModalSalePrice');
        const profitEl = document.getElementById('matModalProfit');

        if (costEl) costEl.textContent = formatRs(metrics.totalCost);
        if (saleEl) saleEl.textContent = formatRs(metrics.sale);
        if (profitEl) {
            profitEl.textContent = formatRs(metrics.profit);
            profitEl.classList.remove('is-positive', 'is-negative');
            profitEl.classList.add(metrics.profit < 0 ? 'is-negative' : 'is-positive');
        }
    }

    function showModal() {
        const modalEl = document.getElementById('variantMaterialsModal');
        if (!modalEl) return;
        if (window.jQuery && typeof window.jQuery(modalEl).modal === 'function') {
            window.jQuery(modalEl).modal('show');
        } else if (window.bootstrap && window.bootstrap.Modal) {
            window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    function hideModal() {
        const modalEl = document.getElementById('variantMaterialsModal');
        if (!modalEl) return;
        if (window.jQuery && typeof window.jQuery(modalEl).modal === 'function') {
            window.jQuery(modalEl).modal('hide');
        } else if (window.bootstrap && window.bootstrap.Modal) {
            window.bootstrap.Modal.getOrCreateInstance(modalEl).hide();
        }
    }

    function openForRow(tr) {
        if (!tr) return;
        activeVariantRow = tr;

        const labelEl = document.getElementById('matModalVariantLabel');
        if (labelEl) labelEl.textContent = buildVariantLabel(tr);

        const variantsBody = document.getElementById('variantsBody');
        const totalVariantsEl = document.getElementById('matModalTotalVariants');
        const applyBtn = document.getElementById('matModalApplyAllBtn');
        const rowCount = variantsBody ? variantsBody.children.length : 1;
        if (totalVariantsEl) totalVariantsEl.textContent = rowCount;
        if (applyBtn) applyBtn.style.display = rowCount > 1 ? '' : 'none';

        const matInp = tr.querySelector('input[name="variant_materials[]"]');
        const list = parseMaterialsJson(matInp ? matInp.value : '[]');
        const rowsContainer = document.getElementById('matModalRows');
        if (rowsContainer) rowsContainer.innerHTML = '';

        if (list.length > 0) {
            list.forEach(item => renderModalItemRow(item.name, item.amount));
        } else {
            renderModalItemRow('', '');
        }

        updateModalSummary();
        showModal();
    }

    document.addEventListener('DOMContentLoaded', function () {
        const variantsBody = document.getElementById('variantsBody');
        const modalEl = document.getElementById('variantMaterialsModal');

        if (variantsBody) {
            variantsBody.addEventListener('click', function (e) {
                const btn = e.target.closest('.variant-materials-btn');
                if (btn) {
                    const tr = btn.closest('tr');
                    if (tr) openForRow(tr);
                }
            });

            variantsBody.addEventListener('input', function (e) {
                if (e.target && (e.target.name === 'variant_sale_price[]' || e.target.name === 'variant_purchase_price[]')) {
                    const tr = e.target.closest('tr');
                    if (tr) updateRowUI(tr);
                }
            });
        }

        function applyMaterialsToRow(tr, list) {
            const matInp = tr.querySelector('input[name="variant_materials[]"]');
            const hadMaterials = parseMaterialsJson(matInp ? matInp.value : '[]').length > 0;
            const totalCost = list.reduce((sum, m) => sum + (parseFloat(m.amount) || 0), 0);

            if (matInp) matInp.value = JSON.stringify(list);

            const purchInp = tr.querySelector('input[name="variant_purchase_price[]"]');
            if (purchInp && (list.length > 0 || hadMaterials)) {
                purchInp.value = Math.round((totalCost + Number.EPSILON) * 100) / 100;
                purchInp.dispatchEvent(new Event('input', { bubbles: true }));
            }

            updateRowUI(tr);
        }

        if (modalEl) {
            const rowsContainer = document.getElementById('matModalRows');
            const addBtn = document.getElementById('matModalAddRowBtn');
            const closeBtn = document.getElementById('matModalCloseBtn');
            const saveBtn = document.getElementById('matModalSaveBtn');
            const applyAllBtn = document.getElementById('matModalApplyAllBtn');

            if (addBtn) {
                addBtn.addEventListener('click', function () {
                    const newRow = renderModalItemRow('', '');
                    newRow?.querySelector('.mat-row-name')?.focus();
                    updateModalSummary();
                });
            }

            if (rowsContainer) {
                rowsContainer.addEventListener('input', updateModalSummary);
                rowsContainer.addEventListener('click', function (e) {
                    const delBtn = e.target.closest('.mat-row-del');
                    if (delBtn) {
                        const row = delBtn.closest('.mat-item-row');
                        if (row) row.remove();
                        if (rowsContainer.children.length === 0) {
                            renderModalItemRow('', '');
                        }
                        updateModalSummary();
                    }
                });
            }

            if (closeBtn) {
                closeBtn.addEventListener('click', hideModal);
            }

            if (saveBtn) {
                saveBtn.addEventListener('click', function () {
                    if (!activeVariantRow) return;
                    const list = collectModalMaterials();
                    applyMaterialsToRow(activeVariantRow, list);
                    hideModal();
                });
            }

            if (applyAllBtn) {
                applyAllBtn.addEventListener('click', function () {
                    if (!variantsBody) return;
                    const list = collectModalMaterials();
                    Array.from(variantsBody.children).forEach(tr => {
                        applyMaterialsToRow(tr, list);
                    });
                    hideModal();
                });
            }
        }
    });

    window.VariantMaterialsManager = {
        buildMaterialsCellHtml,
        buildProfitCellHtml,
        parseMaterialsJson,
        getRowMetrics,
        updateRowUI,
        updateAllRowsUI,
        openForRow,
        formatRs,
        flaskSvg: FLASK_SVG
    };
})();
</script>
