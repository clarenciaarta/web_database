document.addEventListener('DOMContentLoaded', () => {
    const articlesPerPage = 5;
    let currentPage = 1;
    let allArticles = []; 
    let filteredArticles = []; 
    const categoryList = document.getElementById('category-list');
    const articleListContainer = document.getElementById('article-list');
    const searchInput = document.getElementById('search-input');
    const searchButton = document.getElementById('search-button');
    const paginationContainer = document.getElementById('pagination');

    async function fetchData(action, params = {}) {
        let url = `${API_URL}?action=${action}`;
        if (Object.keys(params).length > 0) {
            const queryParams = new URLSearchParams(params).toString();
            url += `&${queryParams}`;
        }

        try {
            const response = await fetch(url);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();
            if (data.status === 'success') {
                return data.data;
            } else {
                console.error('API Error:', data.message);
                return [];
            }
        } catch (error) {
            console.error('Fetch error:', error);
            return [];
        }
    }

    async function renderCategories() {
        const categories = await fetchData('get_categories');
        categoryList.innerHTML = ''; 
        if (categories.length > 0) {
            const allCategoryItem = document.createElement('li');
            const allCategoryLink = document.createElement('a');
            allCategoryLink.href = '#';
            allCategoryLink.textContent = 'Semua Kategori';
            allCategoryLink.dataset.categoryId = 'all';
            allCategoryLink.addEventListener('click', (e) => {
                e.preventDefault();
                filterArticlesByCategory('all'); 
            });
            allCategoryItem.appendChild(allCategoryLink);
            categoryList.appendChild(allCategoryItem);

            categories.forEach(category => {
                const listItem = document.createElement('li');
                const link = document.createElement('a');
                link.href = '#';

                if (category.image_url) {
                    const categoryImage = document.createElement('img');
                    categoryImage.src = `images/${category.image_url}`; 
                    categoryImage.alt = category.name;
                    categoryImage.style.width = '24px';
                    categoryImage.style.height = '24px';
                    categoryImage.style.marginRight = '8px';
                    categoryImage.style.verticalAlign = 'middle';
                    link.appendChild(categoryImage);
                }
                link.appendChild(document.createTextNode(category.name));

                link.dataset.categoryId = category.id;
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    filterArticlesByCategory(category.id);
                });
                listItem.appendChild(link);
                categoryList.appendChild(listItem);
            });
        } else {
            categoryList.innerHTML = '<li>Tidak ada kategori tersedia.</li>';
        }
    }

    async function renderArticles() { 
        articleListContainer.innerHTML = '<h2>Artikel Terbaru</h2>';
        const startIndex = (currentPage - 1) * articlesPerPage;
        const endIndex = startIndex + articlesPerPage;
        const articlesToDisplay = filteredArticles.slice(startIndex, endIndex);

        if (articlesToDisplay.length === 0) {
            const noResults = document.createElement('p');
            noResults.textContent = 'Tidak ada artikel ditemukan.';
            articleListContainer.appendChild(noResults);
        } else {
            articlesToDisplay.forEach(article => {
                const articleDiv = document.createElement('div');
                articleDiv.classList.add('article-item');
                articleDiv.innerHTML = `
                    <h3>${article.title}</h3>
                    <p>Kategori: ${article.category_name || 'Tidak Dikategorikan'}</p>
                    <p>${article.content.substring(0, 150)}...</p>
                    <a href="#" class="read-more">Baca Selengkapnya</a>
                `;
                articleListContainer.appendChild(articleDiv);
            });
        }
        renderPagination();
    }

    function renderPagination() {
        paginationContainer.innerHTML = '';
        const totalPages = Math.ceil(filteredArticles.length / articlesPerPage);

        for (let i = 1; i <= totalPages; i++) {
            const button = document.createElement('button');
            button.textContent = i;
            if (i === currentPage) {
                button.classList.add('active');
            }
            button.addEventListener('click', () => {
                currentPage = i;
                renderArticles(); 
            });
            paginationContainer.appendChild(button);
        }
    }

        let params = {};
        if (categoryId !== 'all') {
            params.category_id = categoryId;
        }
        const articlesByCat = await fetchData('get_articles', params);
        
        filteredArticles = articlesByCat; 
        currentPage = 1; 
        renderArticles(); 
    }

    searchButton.addEventListener('click', async () => {
        const searchTerm = searchInput.value.trim();
        if (searchTerm === '') {
            filteredArticles = [...allArticles];
        } else {
            
            const searchResults = await fetchData('get_articles', { search: searchTerm });
            filteredArticles = searchResults;
        }
        currentPage = 1; 
        renderArticles(); 

    async function initializeApp() {
        allArticles = await fetchData('get_articles');
        filteredArticles = [...allArticles]; 

        renderCategories(); 
        renderArticles();   
    }

    initializeApp();
});