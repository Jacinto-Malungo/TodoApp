<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Rule;
use App\Models\User;
use App\Models\Todo;
use Livewire\WithPagination;

class TodoList extends Component
{
    use WithPagination;

    #[Rule('required|min:3|max:50')]
    public $name;

    public $search;

    public $editingTodoID;

    #[Rule('required|min:3|max:50')]
    public $editingTodoName;


    public function create()
    {
        //=== Steps ===
        //validate
        //create the todo
        //clear the input
        //send flash message
        $validated = $this->validateOnly('name');

        Todo::create($validated);

        $this->reset('name');//Aqui limpamos o campo input no formulário
        request()->session()->flash('success', 'Todo criado com sucesso!');

        $this->resetPage();
    }

    public function delete($todoID)
    {
       
        try {
            Todo::findOrfail($todoID)->delete();
            request()->session()->flash('success', 'Todo eliminado com sucesso!');
        } catch (\Throwable $th) {
           //throw $th;
            request()->session()->flash('error', 'Falha ao eliminar todo!');
            return;
        }
       
    }

    public function edit($todoID)
    {
        $this->editingTodoID = $todoID;
        $this->editingTodoName = Todo::find($todoID)->name;
    }
    
    public function toggle($todoID){
        $todo = Todo::find($todoID);
        $todo->completed = !$todo->completed;
        $todo->save();
    }

    public function cancelEdit()
    {
        $this->reset(['editingTodoID', 'editingTodoName']);
    }

    public function update()
    {
        $this->validateOnly('editingTodoName');

        Todo::find($this->editingTodoID)->update([
            'name' => $this->editingTodoName,
        ]);

        $this->cancelEdit();
        ///request()->session()->flash('successEdtinting', 'Todo atualizado com sucesso!');
    }

    public function render()
    {
        return view('livewire.todo-list', [
            'todos' => Todo::latest()->where('name', 'like', "%{$this->search}%")->paginate(3)
        ]);
    }
}
