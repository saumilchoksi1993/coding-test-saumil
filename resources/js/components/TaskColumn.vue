<template>
  <div class="w-[300px] bg-sky-950 rounded-lg shadow-lg">
    <div
      class="p-4"
      @drop="onDrop($event, props.phase_id)"
      @dragenter.prevent
      @dragover.prevent
    >
      <div class="flex items-center justify-between">
        <h2 class="text-lg text-zinc-100 font-black mb-3">
          {{ kanban.phases[props.phase_id].name }} ({{ kanban.phases[props.phase_id].task_count }})
        </h2>
        <PlusIcon
          @click="createTask()"
          class="mb-3 h-6 w-6 text-white hover:cursor-pointer"
          aria-hidden="true"
        />
      </div>
      <task-card
        v-if="kanban.phases[props.phase_id].tasks.length > 0"
        v-for="task in kanban.phases[props.phase_id].tasks"
        :task="task"
        :key="task"
        draggable="true"
        @dragstart="startDrag($event, task)"
      />

      <!-- A card to create a new task -->
      <div v-if="props.phase_id != 6"
        class="w-full flex items-center justify-between bg-white text-gray-900 hover:cursor-pointer shadow-md rounded-lg p-3 relative"
        @click="createTask()"
      >
        <span>Create a new task</span>
        <PlusIcon class="h-6 w-6" aria-hidden="true" />
      </div>
    </div>
</div>
</template>

<script setup>
// get the props
import { useKanbanStore } from '../stores/kanban'
import { PlusIcon } from '@heroicons/vue/20/solid'

const kanban = useKanbanStore()

const props = defineProps({
    phase_id: {
        type: Number,
        required: true
    },
})

const createTask = function () {
    kanban.creatingTask = true;
    kanban.creatingTaskProps.phase_id = props.phase_id;
};

/* Change Phase id of task while Move */
const startDrag = async (evt, item) => {
  evt.dataTransfer.dropEffect = "move";
  evt.dataTransfer.effectAllowed = "move";
  evt.dataTransfer.setData("itemID", item.id);
  evt.dataTransfer.setData("parentID", item.phase_id);
  evt.dataTransfer.setData("task", item);
};

/* Call Task Update API to change task phase id in DB */
const onDrop = async (evt, phaseId) => {
    const itemID = evt.dataTransfer.getData("itemID");
    const parentID = evt.dataTransfer.getData("parentID");
    const task = kanban.phases[parentID].tasks.find((t) => t.id == itemID);

    if (task) {
        task.phase_id = phaseId;
    }

    try {
        // Update task detail
        await axios.put(`/api/tasks/update/${itemID}`, task);

        // Get task list and update kanban task list
        const response = await axios.get("/api/tasks");
        const originalTasks = response.data;
        kanban.phases = originalTasks.reduce((acc, cur) => {
            acc[cur.id] = cur;
            return acc;
        }, {});
    } catch (error) {
        console.error('There was an error fetching the tasks!', error);
    }
};
</script>
