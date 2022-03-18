<template>
    <default-field :field="field" :errors="errors" :show-help-text="showHelpText">
        <template slot="field">
            <label class="mb-2 block">Type</label>
            <select v-model="type" class="w-full form-control form-input form-input-bordered">
                <option value="manual">Manual URL</option>
                <option value="linked">Linked</option>
            </select>
            <label class="mb-2 mt-4 block">Value</label>
            <div class="">
                <input
                    v-if="type === 'manual'"
                    :id="field.name"
                    type="text"
                    class="w-full form-control form-input form-input-bordered"
                    :class="errorClasses"
                    :placeholder="field.name"
                    v-model="value"
                />
            </div>

            <select v-if="type === 'linked'" class="w-full form-control form-input form-input-bordered">
                <option v-for="value in field.values" v-model="value.id">
                    {{ value.display }}
                </option>
            </select>
        </template>
    </default-field>
</template>

<script>
import {FormField, HandlesValidationErrors} from 'laravel-nova'

export default {
    mixins: [FormField, HandlesValidationErrors],

    props: ['resourceName', 'resourceId', 'field', 'initial_type', 'initial_id'],

    data() {
        return {
            type: (this.initial_type == null) ? 'manual' : 'linked',
            id: this.initial_id
        }
    },

    methods: {
        setInitialValue() {
            this.value = this.field.value || ''
        },

        fill(formData) {
            formData.append(this.field.attribute, this.value || '')
        },
    },
}
</script>
