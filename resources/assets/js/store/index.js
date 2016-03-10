const apiroot = '/api/v1/';
const cache = Object.create(null);
const pageCache = Object.create(null);

export default {

	fetch(query) {
		return new Promise((resolve, reject) => {
			if (cache[query]) {
				resolve(cache[query]);
			} else {
				$.getJSON(apiroot + query, json => {
					const result = cache[query] = json;
					resolve(result);
				}).fail( (jqxhr, text, error) => {
					reject(text);
				});
			}
		});
	},

	fetchPage(page){
		return new Promise((resolve, reject) => {
			if (pageCache[page]) {
				resolve(pageCache[page]);
			} else {
				$.get('api/page/' + page, html => {
					const result = pageCache[page] = html;
					resolve(html);
				}).fail( (jqxhr, text, error) => {
					reject(text);
				});
			}
		})
	},

}