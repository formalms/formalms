import $ from 'jquery';

let initDropdownComplexExecuted = false;

export function changeSons(idSon, idCommon, idUser, idSelected, idCommonCopyRef, registrationLayout) {
    const mandatory = $('#span_for_' + idSon + ' div p label span').hasClass('mandatory') || $('#span_for_' + idSon).hasClass('mandatory');
    const data = {
        id_son: idSon,
        id_common: idCommon,
        id_selected: idSelected,
        id_user: idUser,
        id_common_copy_ref: idCommonCopyRef,
        mandatory: mandatory,
        registration_layout: registrationLayout,
		authentic_request: (document.querySelector(['input[name=authentic_request]'])).value
    };

    $.post(`${window.frontend.config.url.appCore}/ajax.adm_server.php?r=adm/dropdowncomplex/getDropdownComplex`, data, function (response) {
        changedropdownCallback(response, registrationLayout, idSon);
    });
}

function changedropdownCallback(response, registrationLayout, idSon) {
    const field = $('#field_dropdown_complex_' + idSon);
    let preselected = field.val() || 0;

    // Estrai `id_html` usando una regex
    const idHtmlMatch = response.match(/id_html\s*=\s*(\d+);/);
    if (idHtmlMatch) {
        // Parsa `id_html` come numero
        const id_html = parseInt(idHtmlMatch[1], 10);
        console.log("id_html:", id_html); // Puoi usare `id_html` direttamente qui
    } else {
        console.warn("id_html non trovato in response");
    }

    // Split `response` per ottenere il contenuto da visualizzare
    const contentParts = response.split('%%');
    if (contentParts.length > 1) {
        if (registrationLayout) {
            $('#span_for_' + idSon).parent().html(contentParts[1]);
        } else {
            $('#span_for_' + idSon).html(contentParts[1]);
        }
    } else {
        $('#span_for_' + idSon).html(response);
    }

    // Gestione del preselected
    const opt = `#field_dropdown_complex_${idSon} option[value='${preselected}']`;
    if (!$(opt).length) preselected = 0;
    field.val(preselected).change();
}

export function disableSon(idSon, text) {
    const field = $('#field_dropdown_complex_' + idSon);
    field.prop('disabled', true).empty().append(new Option(text, 0));
}

export function initDropdownComplex() {
	console.log("dropdown_complex");
    if (!initDropdownComplexExecuted) {
        $("select[id^='field_dropdown_complex_']").each((_, element) => {
            const father = $(element).parent().data('father');
            if (father > 0 && $(`#field_dropdown_complex_${father}`).val() && $(element).is(':disabled')) {
                $(`#field_dropdown_complex_${father}`).change();
            }
        });
        initDropdownComplexExecuted = true;
    }
}
