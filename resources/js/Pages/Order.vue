<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import type { Rule } from 'ant-design-vue/es/form';
import {reactive, ref, watch} from 'vue';

const rules: Array<Record<string, Rule[]>> = [
    {
        meal: [
            { required: true, message: 'Please Select a meal', trigger: 'change' },
        ],
        people: [
            { required: true, message: 'Please Enter Number of people', trigger: 'change' },
            { type: 'number', max: 10, min: 1 }
        ]
    },
    {
        restaurant: [
            { required: true, message: 'Please Select a restaurant', trigger: 'change' },
        ],
    },
];

const currentStep = ref<number>(1);

const meals = ref<Array<string>>([]);

const errorMessage = ref<string>('');

const restaurants = ref<any>([]);

const dishes = ref<any>([]);

const formState = reactive<any>({
    meal: '',
    people: '',
    restaurant: '',
    dishes: [
        {
            name: '',
            order: '',
        }
    ]
});

const getMeal = async () => {
    const { data } = await window.axios.get(route('dishes', { getMeal: true }));
    meals.value = data;
}

const getRestaurant = async () => {
    const { data } = await window.axios.get(route('dishes', { meal: formState.meal }));
    restaurants.value = data;
}

const getDish = async () => {
    const { data } = await window.axios.get(route('dishes', { searchMeal: formState.meal, restaurant: formState.restaurant }));
    dishes.value = data;
}

watch(() => currentStep.value, () => {
    errorMessage.value = '';
    if (currentStep.value == 2) {
        formState.restaurant = '';
        getRestaurant();
    }
    if (currentStep.value == 3) {
        formState.dishes = [
            {
                name: '',
                order: '',
            }
        ];
        getDish();
    }
});

getMeal();

const onFinish = () => {
    if (currentStep.value == 3 && formState.dishes.length < formState.people) {
        return errorMessage.value = 'The number of meal is lesser than the people'
    }
    currentStep.value++;
}

const back = () => {
    currentStep.value--;
}

const addMeal = () => {
    if (formState.dishes.length >= formState.people) {
        return errorMessage.value = 'The number of meal is greater than the people'
    }
    formState.dishes.push({
        name: '',
        order: '',
    });
}
</script>
<template>
    <GuestLayout>
        <Head title="Order food" />

        <a-tabs v-model:activeKey="currentStep" type="card">
            <a-tab-pane
                :key="index"
                :tab="`Step ${index}`"
                v-for="(index) in 4"
                disabled
            />
        </a-tabs>
            <a-form
                :model="formState"
                name="basic"
                :label-col="{ span: 10 }"
                :wrapper-col="{ span: 14 }"
                autocomplete="off"
                :rules="rules[currentStep - 1]"
                @finish="onFinish"
            >
                <div v-if="currentStep == 1">
                    <a-form-item ref="meal" name="meal" label="Please Select a meal">
                        <a-select v-model:value="formState.meal">
                            <a-select-option
                                :value="meal"
                                :key="index"
                                v-for="(meal, index) in meals"
                            >{{ meal }}</a-select-option>
                        </a-select>
                    </a-form-item>
                    <a-form-item ref="people" name="people" label="Please Enter Number of people">
                        <a-input-number v-model:value="formState.people" />
                    </a-form-item>
                </div>

                <div v-if="currentStep == 2">
                    <a-form-item ref="restaurant" name="restaurant" label="Please Select a restaurant">
                        <a-select v-model:value="formState.restaurant">
                            <a-select-option
                                :value="restaurant.restaurant"
                                :key="index"
                                v-for="(restaurant, index) in restaurants"
                            >{{ restaurant.restaurant }}</a-select-option>
                        </a-select>
                    </a-form-item>
                </div>

                <div v-if="currentStep == 3">
                    <div
                        v-for="(dish, index) in formState.dishes"
                        :key="index"
                        class="flex justify-between"
                    >
                        <a-form-item
                            class="w-1/2"
                            :name="['dishes', index, 'name']"
                            label="Please Select a meal"
                            :rules="[{ required: true }]"
                        >
                            <a-select v-model:value="dish.name">
                                <a-select-option
                                    :value="dish.name"
                                    :key="index"
                                    v-for="(dish, index) in dishes"
                                >{{ dish.name }}</a-select-option>
                            </a-select>
                        </a-form-item>
                        <div class="w-1/2">
                            <a-form-item
                                :name="['dishes', index, 'order']"
                                label="Please Enter no of servings"
                                :rules="[{ required: true }]"
                            >
                                <a-input-number
                                    v-model:value="dish.order"
                                    :min="1"
                                    :max="formState.people"
                                />
                            </a-form-item>
                        </div>
                    </div>
                    <div v-if="errorMessage" class="text-red-600">{{ errorMessage }}</div>
                    <div class="flex justify-center items-center">
                        <a-button @click="addMeal">ADD</a-button>
                    </div>
                </div>


                <div class="flex justify-between items-center">
                    <div>
                        <a-button @click="back" v-if="currentStep > 1">Previous</a-button>
                    </div>
                    <a-button html-type="submit">Next</a-button>
                </div>
            </a-form>
    </GuestLayout>
</template>
