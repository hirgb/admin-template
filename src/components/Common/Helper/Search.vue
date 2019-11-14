<template lang="xtmin">
iframe :src="iframeSrc"
</template>

<script>

export default {
    data() {
        return {
            iframeSrc: 'https://m.baidu.com/s?word=',
        }
    },
    methods: {
        search(word) {
            this.iframeSrc = 'https://m.baidu.com/s?word=' + word
        },
    }
}
</script>
