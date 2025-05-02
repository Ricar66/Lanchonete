// Função para formatar campos de preço
document.addEventListener('DOMContentLoaded', function() {
    // Formatador de preço
    const formatarPreco = (input) => {
        let valor = input.value.replace(/\D/g, '');
        valor = (valor / 100).toFixed(2) + '';
        valor = valor.replace('.', ',');
        valor = valor.replace(/(\d)(?=(\d{3})+\,)/g, '$1.');
        input.value = valor;
    };
    
    // Aplicar a todos os campos de preço
    document.querySelectorAll('input[name="preco"], input[name="novo_preco"]').forEach(input => {
        input.addEventListener('keyup', () => formatarPreco(input));
    });
    
    // Máscara para telefone
    const mascaraTelefone = (input) => {
        let valor = input.value.replace(/\D/g, '');
        
        if (valor.length > 11) {
            valor = valor.substring(0, 11);
        }
        
        // Aplica a máscara (99) 99999-9999
        if (valor.length > 2) {
            valor = '(' + valor.substring(0, 2) + ') ' + valor.substring(2);
        }
        if (valor.length > 10) {
            valor = valor.substring(0, 10) + '-' + valor.substring(10);
        }
        
        input.value = valor;
    };
    
    // Aplicar a todos os campos de telefone
    document.querySelectorAll('input[name="telefones[]"]').forEach(input => {
        input.addEventListener('keyup', () => mascaraTelefone(input));
    });
    
    // Máscara para CEP
    const mascaraCEP = (input) => {
        let valor = input.value.replace(/\D/g, '');
        
        if (valor.length > 8) {
            valor = valor.substring(0, 8);
        }
        
        // Aplica a máscara 99999-999
        if (valor.length > 5) {
            valor = valor.substring(0, 5) + '-' + valor.substring(5);
        }
        
        input.value = valor;
    };
    
    // Aplicar ao campo de CEP
    document.querySelector('input[name="cep"]')?.addEventListener('keyup', () => mascaraCEP(document.querySelector('input[name="cep"]')));
});