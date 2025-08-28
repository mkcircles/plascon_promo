<template>
    <div class="flex justify-end">
		<ul class="pagination bg-white p-2 shadow-sm rounded">
			<!-- <li class="pagination-item">
				<span
					class="rounded-l rounded-sm border border-gray-100 px-3 py-2 cursor-not-allowed no-underline text-gray-600 h-10"
					v-if="isInFirstPage"
				>&laquo;</span>
				<a
					v-else
					@click.prevent="onClickFirstPage"
					class="rounded-l rounded-sm border-t border-b border-l border-gray-100 px-3 py-2 text-gray-600 hover:bg-gray-100 no-underline"
					href="#"
					role="button"
					rel="prev"
				>
					&laquo;
				</a>
			</li>

			<li class="pagination-item">
				<button
					type="button"
					@click="onClickPreviousPage"
					:disabled="isInFirstPage"
					aria-label="Go to previous page"
					class="rounded-sm border border-gray-100 px-3 py-2 hover:bg-gray-100 text-gray-600 no-underline mx-2 text-sm"
					:class="{'cursor-not-allowed': isInFirstPage}"
				>Previous</button>
			</li> -->

			<li
				v-for="page in pages"
				class="pagination-item"
				:key="page.name"
			>
                
				<span
					class="rounded-sm border border-blue-100 px-3 py-2 bg-blue-100 no-underline text-blue-500 cursor-not-allowed"
					v-if="isPageActive(page.name)"
				>{{ page.name }}</span>
				<a
					class="rounded-sm border border-gray-100 px-3 py-2 hover:bg-gray-100 text-gray-600 no-underline"
					href="#"
					v-else
					@click.prevent="onClickPage(page.name)"
					role="button"
				>{{ renderPageName(page.name) }}</a>
			</li>

			<!-- <li class="pagination-item">
				<button
					type="button"
					@click="onClickNextPage"
					:disabled="isInLastPage"
					aria-label="Go to next page"
					class="rounded-sm border border-gray-100 px-3 py-2 hover:bg-gray-100 text-gray-600 no-underline mx-2 text-sm"
					:class="{'cursor-not-allowed': isInLastPage}"
				>Next</button>
			</li>

			<li class="pagination-item">
				<button
					type="button"
					@click="onClickLastPage"
					:disabled="isInLastPage"
					aria-label="Go to last page"
				>Last</button> 
				<a
					class="rounded-r rounded-sm border border-gray-100 px-3 py-2 hover:bg-gray-100 text-gray-600 no-underline"
					href="#"
					@click.prevent="onClickLastPage"
					rel="next"
					role="button"
					v-if="hasMorePages"
				>&raquo;</a>
				<span
					class="rounded-r rounded-sm border border-gray-100 px-3 py-2 hover:bg-gray-100 text-gray-600 no-underline cursor-not-allowed"
					v-else
				>&raquo;</span>
			</li> -->
		</ul>
	</div>
  
</template>

<script setup>
    import {computed} from "vue";

    const props = defineProps(['links', 'currentPage','lastPage']);
    const emit= defineEmits(["pagechanged"])

    const pages = computed(() => {
        return props.links.map(link => {
            if (link.label !== "Next &raquo;" || link.label !== "&laquo; Previous") {
                return {
                    name: link.label,
                    url: link.url,
                    isDisabled: link.active
                }
            }  
        })
    })

    const renderPageName = (text) => {
		//separate strings by space
		const words = text.split(' ');
		//check if length is greater than 1
		if (words.length > 1 && words[0] === '&laquo;') {
			return words[1];
		}
		else if(words.length > 1 && words[0] === 'Next'){
			return words[0];
		}
		else
			return text;
    }

    const onClickFirstPage = () => {
        const firstPage = pages.value[0];
        if (firstPage.url) {
            window.location.href = firstPage.url;
        }
    }
    const onClickLastPage = () => {
        const lastPage = pages.value[pages.value.length - 1];
        if (lastPage.url) {
            window.location.href = lastPage.url;
        }
    }
   
    const onClickPage = (page) => {
        const selectedPage = pages.value.find(p => p.name === page);
        if(selectedPage.name === "Next &raquo;"){
            if(props.currentPage >= props.lastPage){
                emit("pagechanged", props.lastPage);
            }
            else{
                emit("pagechanged", props.currentPage + 1);
            }
           
        }
        else if(selectedPage.name === "&laquo; Previous"){
            if(props.currentPage <= 1){
                emit("pagechanged", props.currentPage);
            }
            else{
                emit("pagechanged", props.currentPage - 1);
            }
        }
        else{
            emit("pagechanged", selectedPage.name);
        }
        
       
    }
    const isPageActive = (pageName) => {
        const activePage = pages.value.find(page => page.isDisabled);
        return activePage.name === pageName;
    }
    const isInFirstPage = computed(() => {
        const activePage = pages.value.find(page => page.isDisabled);
        return activePage.name === pages.value[0].name;
    })
    const isInLastPage = computed(() => {
        const activePage = pages.value.find(page => page.isDisabled);
        return activePage.name === pages.value[pages.value.length - 1].name;
    })
    const hasMorePages = computed(() => {
        return pages.value.length > 1;
    })


    

  
    

</script>

<style>
    .pagination {
  list-style-type: none;
}

.pagination-item {
  display: inline-block;
}

.active {
  /* @apply .border-t .border-b .border-l .border-blue-100 .px-3 .py-2 .bg-blue-100 .no-underline .text-blue-500 .text-sm; */
}
</style>