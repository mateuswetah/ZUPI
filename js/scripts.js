// Checks if document is loaded
const performWhenDocumentIsLoaded = callback => {
    if (/comp|inter|loaded/.test(document.readyState))
        cb();
    else
        document.addEventListener('DOMContentLoaded', callback, false);
}

function updatePlaceholderFiles() {
    wp.hooks.addFilter('tainacan_get_the_mime_type_icon', 'zupi' , (imageSrc, documentType, size) => {
        return zupi.images_directory + '/artist_placeholder.gif';
    });
};

performWhenDocumentIsLoaded(() => {

    updatePlaceholderFiles();

    relatedItems = document.getElementsByClassName('wp-block-tainacan-related-items');

    if ( !relatedItems || relatedItems.length === 0 )
        return;

    relatedItems = relatedItems[0] && relatedItems[0].children ? relatedItems[0].children : [];
    
    if ( relatedItems.length === 0 )
        return;
    
    for (let relatedItem of relatedItems) {
        let relatedItemButton = relatedItem.getElementsByClassName('wp-block-button__link')[0];

        if ( relatedItemButton && relatedItem.dataset ) {
            
            let allLabel = 'todos os';
            let itemsLabel = '';
            switch ( relatedItem.dataset.relatedCollectionId ) {
                case '267':
                    allLabel = 'todas as';
                    itemsLabel = 'fotos';
                    break;
                case '5054':
                    allLabel = 'todas as';
                    itemsLabel = 'revistas';
                    break;
                case '20':
                    allLabel = 'todas as';
                    itemsLabel = 'obras';
                    break;
                case '5':
                    itemsLabel = 'artistas';
                    break;
                case '1507':
                    itemsLabel = 'eventos';
                    break;
                case '1486':
                    itemsLabel = 'locais';
                    break;
                default:
                    itemsLabel = 'itens';
            }
            relatedItemButton.innerText = relatedItemButton.innerText.replace('todos os', allLabel);
            relatedItemButton.innerText = relatedItemButton.innerText.replace('itens relacionados', itemsLabel);
            
        }
    }
});