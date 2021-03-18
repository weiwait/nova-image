<template>
  <default-field :field="field" :errors="errors" :show-help-text="showHelpText">
    <template slot="field">
      <input
        :id="field.name"
        type="hidden"
        class="w-full form-control form-input form-input-bordered"
        :class="errorClasses"
        :placeholder="field.name"
        v-model="value"
      />
      <input
        id="originalName"
        type="hidden"
        class="w-full form-control form-input form-input-bordered"
        :class="errorClasses"
        placeholder="文件原名"
        v-model="originalName"
      />

        <input ref="dragUploadImg" type="file" style="position:absolute; clip-path: circle(0);" accept="image/png, image/jpeg, image/gif, image/jpg"
               @change="uploadImg($event)">

        <div id="weiwait-modal" v-if="showCropper">
            <div>
            </div>
            <vue-cropper
                ref="cropper"
                v-bind="cropper"
                class="cropper-container">
            </vue-cropper>
            <div class="footer">
                <input class="btn btn-xs btn-primary inline-block items-center mr-2" type="button" v-on:click="startCrop()" value="裁剪">
                <label ref="uploadImg" style="line-height: 2.15" class="btn btn-xs btn-primary inline-block items-center mr-2" for="uploads">更换图片</label>
                <input type="file" id="uploads" style="position:absolute; clip-path: circle(0);" accept="image/png, image/jpeg, image/gif, image/jpg"
                       @change="uploadImg($event)">
                <button type="button" class="btn btn-xs btn-primary inline-block items-center mr-2" v-on:click="loopMultiple()">倍数 {{cropper.enlarge}}</button>
                <input class="btn btn-xs btn-primary inline-block items-center mr-2" type="button" v-on:click="closeCrop()" value="关闭">
            </div>
        </div>

        <div v-if="!preview"
             ref="dragRegion"
             v-bind:class="{'drag-enter': dragEnter}"
             id="drag-region"
             @click.stop="dragRegionClick"
             @dragleave.prevent="dragLeave"
             @dragenter.prevent="dragEntering"
             @dragover.prevent="dragOver"
             @drop.prevent="drop($event)"
        >
            点击或拖拽文件到此
        </div>

        <div v-else class="img-preview" @click="switchCrop()"
             @mouseover="hover=true"
             @mouseleave="hover=false"
        >
            <img class="weiwait-img" :src="previewUrl" alt="">
            <span
                :class="['delete', {hover: hover}]"
                @click.stop="deleteItem()"
            >
                移除
            </span>
        </div>

    </template>
  </default-field>
</template>

<script>
import { FormField, HandlesValidationErrors } from 'laravel-nova'
import { VueCropper } from 'vue-cropper'

export default {
  mixins: [FormField, HandlesValidationErrors],

    data() {
      return {
          originalName: '',
          dragEnter: false,
          cropper: {
              img: '',
              outputType: 'png',
              autoCrop: true,
              enlarge: 1,
          },
          showCropper: false,
          preview: false,
          previewUrl: '',
          hover: false,
      }
    },

  props: ['resourceName', 'resourceId', 'field'],

  methods: {
    /*
     * Set the initial, internal value for the field.
     */
    setInitialValue() {
        this.value = this.field.value || ''
        this.cropper = {...this.cropper, ...this.field.cropper}
        if (this.value) {
            this.preview = true
            this.showCropper = false
            this.cropper.img = this.field.previewUrl
            this.previewUrl = this.field.previewUrl
        }

    },

    /**
     * Fill the given FormData object with the field's internal value.
     */
    fill(formData) {
      formData.append(this.field.attribute, this.value || '')
      formData.append('originalName', this.originalName || '')
    },
      startCrop() {
        this.$refs.cropper.startCrop()
        this.$refs.cropper.getCropData(data=>{
            this.value = data
            this.preview = true
            this.showCropper = false
            this.previewUrl = data
        })
      },
      switchCrop() {
        this.cropper.img = this.cropper.img || this.previewUrl || this.field.previewUrl || ''
        this.showCropper = true
      },
      uploadImg(e) {
          if (e.target.files.length !== 1) return;

          this.cropping(e.target.files[0])
      },
      closeCrop() {
        this.showCropper = false
          this.preview = true
      },
      loopMultiple() {
        this.cropper.enlarge >= 3 ? this.cropper.enlarge = 1 : this.cropper.enlarge++
      },
      dragEntering() {
          this.dragEnter = true
      },
      dragLeave() {
          this.dragEnter = false
      },
      dragOver() {
          // this.dragEnter = false
      },
      dragRegionClick() {
          this.$refs.dragUploadImg.click()
      },
      drop(e) {
          e.preventDefault()
          const files = e.dataTransfer.files
          this.dragEnter = false
          if (files.length < 1) return;

          this.cropping(files[0])

          this.dragEnter = false
      },
      cropping(file) {
          this.originalName = file.name

          let reader = new FileReader()
          reader.readAsDataURL(file);
          reader.onload = e => {
              this.cropper.img = e.target.result
              this.value = e.target.result
              this.previewUrl = e.target.result
              this.showCropper = true;
          }
      },
      deleteItem() {
          this.previewUrl = ''
          this.value = ''
          this.preview = false
      },
  },
    mounted() {
      this.cropper.img = this.previewUrl || ''
    },
    components: {
        VueCropper,
    },
}
</script>

<style lang="scss" scoped>
#weiwait-modal {
    width: 640px;
    height: 420px;
    background-color: #ffffff;
    position: fixed;
    top: 25%;
    left: 38%;
    z-index: 9;
    box-shadow: 12px 12px 80px -12px;
    .footer {
        height: 60px;
        line-height: 60px;
        padding-left: .5rem;
    }
}
.img-preview {
    max-width: 270px;
    cursor: pointer;
    position: relative;
}
.delete {
    width: 40px;
    height: 40px;
    opacity: 1;
    position: absolute;
    right: 0;
    bottom: 0;
    text-align: center;
    line-height: 40px;
    cursor: pointer;
    display: none;
}
.hover {
    display: block;
    color: #d91414;
}
</style>
