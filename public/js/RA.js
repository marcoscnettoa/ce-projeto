// # -
var RA = {
    onload: function(){
        RA.load.cnpj_v2($('.cnpj_v2'));
        RA.load.cep_v2($('.cep_v2'));
        RA.load.money_v2($('.money_v2'));
        RA.load.componenteData_v2($('.componenteData_v2'));
        RA.load.componenteDataHora_v2($('.componenteDataHora_v2'));
        RA.load.select_single_no_trigger($('.select_single_no_trigger'));
        RA.load.select_tags($('.select_tags'));
        RA.load.grid_remove_v2($(".grid_remove_v2"));
        $('.select_single, .select_single_no_trigger').each(function(i,e){
            if($(e).attr('dropdown-menu-right') != undefined){
                $(e).parents('.form-group').find('div.dropdown-menu').addClass('dropdown-menu-right');
            }
        });
        $(".multiple_add_v2").click(function() {
            var divclass = $(this).attr('data');
            var html    = $("." + divclass).find('.divdefault').html();
            $("." + divclass).attr('count',Number($("." + divclass).attr('count'))+1);
            var div     = '<div class="dinamicField'+$("." + divclass).attr('count')+' item">';
            var count   = $("." + divclass).attr('count');
            div         = div + html + '</div>';
            var dinamic = $("." + divclass).append( div );
            $(dinamic).find('.dinamicField' + count).find('.bootstrap-select').remove();
            RA.load.select_single_no_trigger($(dinamic).find('.dinamicField' + count).find('.select_single_no_trigger'));
            RA.load.money_v2($(dinamic).find('.dinamicField' + count).find('.money_v2'));
            RA.load.grid_remove_v2($(dinamic).find('.dinamicField' + count).find('.grid_remove_v2'));
            $(dinamic).find('.dinamicField' + count).find('select').val('').trigger('change');
            $(dinamic).find('.dinamicField' + count).find('input').val('');
        });
        $(".excluir-auto-confirma").on("click", function() {
            let modulo     = ($(this).attr('modulo')!=undefined && $(this).attr('modulo') != ""?$(this).attr('modulo'):undefined);
            let modulo_id  = ($(this).attr('modulo_id')!=undefined && $(this).attr('modulo_id') != ""?$(this).attr('modulo_id'):undefined);

            if(modulo == undefined || modulo_id == undefined){ return false; }

            if(confirm('Tem certeza que quer deletar?')){
                let form    = document.createElement('form');
                form.method = 'POST';
                form.action = base+'/'+modulo+'/'+modulo_id;
                let input   = document.createElement('input');
                input.type  = 'hidden';
                input.name  = '_token';
                input.value = $("meta[name=\'csrf-token\']").attr('content');
                form.appendChild(input);
                input       = document.createElement('input');
                input.type  = 'hidden';
                input.name  = '_method';
                input.value = 'DELETE';
                form.appendChild(input);
                $('body').append(form);
                form.submit();
            }
        });
    },
    load: {
        form_acoes: function(E){
            E.on('click',function(){
                let modulo          = ($(this).attr('modulo')!=undefined && $(this).attr('modulo') != ""?$(this).attr('modulo'):undefined);
                let modulo_id       = ($(this).attr('modulo_id')!=undefined && $(this).attr('modulo_id') != ""?$(this).attr('modulo_id'):undefined);
                let modulo_method   = ($(this).attr('modulo_method')!=undefined && $(this).attr('modulo_method') != ""?$(this).attr('modulo_id'):undefined);
                let modulo_url      = ($(this).attr('modulo_url')!=undefined && $(this).attr('modulo_url') != ""?$(this).attr('modulo_url'):undefined);
                let modulo_redirect = ($(this).attr('modulo_redirect')!=undefined && $(this).attr('modulo_redirect') != ""?$(this).attr('modulo_redirect'):undefined);

                if(modulo == undefined || modulo_id == undefined || modulo_url == undefined || modulo_method == undefined){ return false; }

                let form    = document.createElement('form');
                form.method = (modulo_method=='DELETE'?'POST':modulo_method);
                form.action = base+'/'+modulo_url;
                if(modulo_redirect != 'true'){
                    let input       = document.createElement('input');
                    input.type  = 'hidden';
                    input.name  = '_token';
                    input.value = $("meta[name=\'csrf-token\']").attr('content');
                    form.appendChild(input);
                    input       = document.createElement('input');
                    input.type  = 'hidden';
                    input.name  = '_method';
                    input.value = modulo_method;
                    form.appendChild(input);
                }
                $('body').append(form);
                form.submit();
            })
        },
        cnpj_v2: function(E){
            E.mask('99.999.999/9999-99');
        },
        cep_v2: function(E){
            E.mask('99999-999');
        },
        money_v2: function(E){
            E.each(function(i,e){
                $(e).maskMoney({ decimal: ",", thousands: ".", precision: ($(e).attr('maskMoney_precision')!=undefined?Number($(e).attr('maskMoney_precision')):2) });
            });
        },
        componenteData_v2: function(E){
            E.mask('99/99/9999');
            E.datepicker({ format: 'dd/mm/yyyy', language: 'pt-BR' });
        },
        componenteDataHora_v2: function(E){
            E.mask('99/99/9999 99:99');
            E.datetimepicker({ format: 'dd/mm/yyyy hh:ii', autoclose: true, language: 'pt-BR' });
        },
        select_single_no_trigger: function(E){
            E.selectpicker({
                noneSelectedText : '',
                //dropdownAlignRight : true,
                noneResultsText: 'Nenhum resultado encontrado'
            });
        },
        select_tags: function(E){
            E.each(function(i,e){
                $(e).tagsinput({
                    maxTags: ($(e).attr('maxTags')!=undefined?$(e).attr('maxTags'):3)
                });
            });
        },
        grid_remove_v2: function(E){
            E.click(function() {
                $(this).parents('.item').remove();
            });
        }
    },
    consulta: {
        cnpj: async function(v){
            let cnpj = v.replaceAll('.','').replaceAll('/','').replaceAll('-','');
            if(cnpj == ""){ return undefined; }
            try {
                return await $.get('https://dashboard.xxxxrxxxapps.com/v1/cnpj/'+cnpj, function(d,s){
                    if(d!=undefined && Object.keys(d).length){ return d; }else { return undefined; }
                });
            }catch(e){
                //console.log('Erro: '+e);
            }
        },
        cep: async function(v){
            let cep = v.replaceAll('.','').replaceAll('-','');
            if(cep == ""){ return undefined; }
            try {
                return await $.get('https://dashboard.xxxxrxxxapps.com/v1/cep/'+cep, function(d,s){
                    if(d!=undefined && Object.keys(d).length){ return d; }else { return undefined; }
                });
            }catch(e){
                //console.log('Erro: '+e);
            }
        }
    },
    format: {
        Decimal_DB_ptBR: function(v, mfd = 2){
            v = ((v!='' && v!=null)?v:0);
            v = (typeof(v)=='string'?parseFloat(v):(!isNaN(v)?v:0));
            v = v.toFixed(2);
            return (new Intl.NumberFormat('pt-BR',{minimumFractionDigits:mfd})).format(v);
        },
        Decimal_ptBR_DB: function(v, mfd = 2){
            let pf = parseFloat(v.replaceAll('.','').replaceAll(',','.'));
            if(isNaN(pf)){ return 0; }
            return pf;
        },
        Data_ptBR_DB: function(v){
            if(v.length != 10){ return ''; }
            let v_split = v.split('/').reverse().join('-');
            return v_split;
        },
        Data_DB_ptBR: function(v){
            if(v.length != 10){ return ''; }
            let v_split = v.split('-').reverse().join('/');
            return v_split;
        },
        Data_Prox_Mes: function(f,v,r){
            let v_split = '';
            let d_dia   = '';
            let d_mes   = '';
            let d_ano   = '';

            if(f == 'DB'){
                v_split = v.split('-');
                d_dia   = v_split[2].padStart(2, '0');
                d_mes   = v_split[1].padStart(2, '0');
                d_ano   = v_split[0];
            }else if(f== 'ptBR'){
                v_split = v.split('/');
                d_dia   = v_split[0].padStart(2, '0');
                d_mes   = v_split[1].padStart(2, '0');
                d_ano   = v_split[2];
            }

            if(v_split.length != 3){ console.log("chegou!"); return ''; }

            let v_data  = new Date(Number(d_ano), Number(d_mes) - 1, Number(d_dia));
            v_data.setMonth(v_data.getMonth() + 1);

            let prox_mes = v_data.getMonth() + 1;
            let prox_ano = v_data.getFullYear();
            if(prox_mes > 12){
                prox_mes = 1;
                prox_ano++;
            }

            prox_mes = String(prox_mes).padStart(2,'0');

            if(r == 'DB'){
                return prox_ano + '-'+ prox_mes + '-' + String(v_data.getDate()).padStart(2,'0');
            }else if(r == 'ptBR'){
                return String(v_data.getDate()).padStart(2,'0') + '/'+ prox_mes + '/' + prox_ano;
            }else {
                return '';
            }

            /*console.log(v_split);
            console.log('Dia:' + d_dia);
            console.log('Mês:' + d_mes);
            console.log('Ano:' + d_ano);*/
        }
    }
}
RA.onload();
