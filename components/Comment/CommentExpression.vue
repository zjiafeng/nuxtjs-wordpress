<template>
  <div v-show="visible" ref="expression" class="expression-wrapper">
    <div v-if="!expressionList.length" class="loading">
      <x-icon type="icon-loading"></x-icon>
      <p>加载中...</p>
    </div>
    <template v-else>
      <ul class="expression-list">
        <li v-for="(tabs, index) in expressionList" :key="index" v-show="active === index" class="item">
          <span
            v-for="(item, index) in tabs.list"
            :key="index"
            :title="item.title"
            @click.stop="choose(`[${item.code}]`)"
          >
            <img :src="item.url" :alt="item.title" width="20">
          </span>
        </li>
      </ul>
      <ul class="tabs-wrapper">
        <li
          v-for="(tabs, index) in expressionList"
          :key="index"
          :class="['tabs-item', active === index && 'is-active']"
          @click.stop="active = index"
        >
          {{ tabs.text }}
        </li>
      </ul>
    </template>
  </div>
</template>
<script>
import { mapActions, mapState } from 'vuex'

export default {
  name: 'CommentExpression',

  props: {
    visible: {
      required: true,
      type: Boolean,
      defaults: false
    }
  },

  computed: {
    ...mapState('comment', ['expressionList'])
  },

  watch: {
    visible(visible) {
      if (visible && !this.expressionList.length) this.getExpression()
    }
  },

  data() {
    return {
      active: 0
    }
  },

  mounted() {
    document.body.addEventListener('click', this.close, false)
  },

  beforeDestroy() {
    document.body.removeEventListener('click', this.close, false)
  },

  methods: {
    ...mapActions('comment', ['getExpression']),
    choose(value) {
      this.$emit('on-change', value)
      this.$emit('update:visible', false)
    },

    close() {
      this.visible && this.$emit('on-close')
    }
  }
}
</script>
<style lang="scss" scoped>
// 表情容器
.expression-wrapper {
  box-sizing: border-box;
  position: absolute;
  top: 30px;
  left: 0;
  z-index: 2;
  width: 410px;
  max-width: 100%;
  height: 300px;
  background: var(--color-main-background);
  box-shadow: $box-shadow;
  border-radius: $border-radius;

  &:after {
    content: "";
    display: inline-block;
    width: 100%;
  }

  .loading {
    position: absolute;
    top: 50%;
    left: 50%;
    text-align: center;
    color: $color-theme;
    transform: translate(-50%, -50%);
  }

  .expression-list {
    height: calc(100% - 40px);
    padding: 10px;
    box-sizing: border-box;
    overflow-y: scroll;
    -webkit-overflow-scrolling: touch;
  }

  .tabs-wrapper {
    // position: absolute;
    // bottom: 0;
    display: flex;
    align-items: center;
    width: 100%;
    height: 40px;
    padding: 0 10px;
    box-sizing: border-box;
    border-top: 2px solid var(--color-border);
    line-height: 40px;

    .tabs-item {
      padding: 0 10px;
      border-right: 2px solid var(--color-border);
      cursor: pointer;

      &.is-active {
        color: $color-theme;
      }
    }
  }

  span {
    display: inline-block;
    width: 40px;
    height: 40px;
    margin: 4px;
    border-radius: $border-radius;
    background: var(--color-sub-background);
    text-align: center;
    line-height: 40px;
    cursor: pointer;
  }

  img {
    vertical-align: middle;
  }
}
</style>
