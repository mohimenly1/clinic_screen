package com.clinic.screen.ui.inquiry

import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.ImageView
import android.widget.TextView
import androidx.cardview.widget.CardView
import androidx.recyclerview.widget.RecyclerView
import com.clinic.screen.R
import com.clinic.screen.data.model.Schedule

class DaysAdapter(
    private val dayGroups: List<DayScheduleGroup>,
    private val onDayClick: (DayScheduleGroup) -> Unit
) : RecyclerView.Adapter<DaysAdapter.DayViewHolder>() {

    data class DayScheduleGroup(
        val day: String,
        val dayLabel: String,
        val schedules: List<Schedule>
    )

    inner class DayViewHolder(itemView: View) : RecyclerView.ViewHolder(itemView) {
        val dayCard: CardView = itemView.findViewById(R.id.dayCard)
        val dayLabelTextView: TextView = itemView.findViewById(R.id.dayLabel)
        val schedulesCountTextView: TextView = itemView.findViewById(R.id.schedulesCount)
        val calendarIcon: ImageView = itemView.findViewById(R.id.calendarIcon)
    }

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): DayViewHolder {
        val view = LayoutInflater.from(parent.context)
            .inflate(R.layout.item_day_clickable, parent, false)
        return DayViewHolder(view)
    }

    override fun onBindViewHolder(holder: DayViewHolder, position: Int) {
        val dayGroup = dayGroups[position]
        
        holder.dayLabelTextView.text = dayGroup.dayLabel
        holder.schedulesCountTextView.text = "(${dayGroup.schedules.size})"
        
        holder.dayCard.setOnClickListener {
            onDayClick(dayGroup)
        }
    }

    override fun getItemCount(): Int = dayGroups.size
}

